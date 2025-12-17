<?php

namespace AgentSystem;

use AgentSystem\AI\AI;
use AgentSystem\Memory\Memory;
use AgentSystem\Tools\Tool;

class Agent
{
  private $ai;
  private $memory;
  private $tools = [];
  private $config;
  private $maxRetries;
  private $currentStep = 0;
  private $startTime;
  private $maxRuntime = 3600; // 1 hour default max runtime
  private $interrupted = false;
  
  public function __construct(AI $ai, array $config)
  {
    $this->ai = $ai;
    $this->config = $config;
    $this->maxRetries = $config['maxRetries'] ?? 3;
    $this->maxRuntime = $config['maxRuntime'] ?? 3600;
  }
  
  public function addTool(Tool $tool): void
  {
    $this->tools[$tool->getName()] = $tool;
  }
  
  public function setMemory(Memory $memory): void
  {
    $this->memory = $memory;
    $this->ai->setMemory($memory);
  }
  
  public function achieveGoal(string $goal): array
  {
    $this->startTime = time();
    
    if($this->config['clarificationEnabled'] ?? true)
      $this->clarifyGoal($goal);

    // TASK: missing "Strategy" see original prompt

    $plan = $this->createPlan($goal);
    $plan = $this->prioritizeTasks($plan);
    
    if($this->config['improvementEnabled'] ?? true)  // TASK: only in loop see original prompt
      $plan = $this->analyzePlanForImprovements($plan);
    
    while( ! $this->isGoalAchieved($goal) && $this->canContinue())
    {
      $nextTask = $this->getNextTask($plan);
      if($nextTask === null)
        break;
        
      $success = $this->executeTask($nextTask);
      
      if($success)  
        $success = $this->verifyTask($nextTask);
      
      // TASK: missing "Goal check" see original prompt

      if( ! $success)
      {
        if($this->currentStep >= $this->maxRetries)
          throw new \RuntimeException("Failed to execute task after {$this->maxRetries} attempts");
          
        $plan = $this->improvePlan($plan, $nextTask);
        if($this->config['improvementEnabled'] ?? true)
          $plan = $this->analyzePlanForImprovements($plan);
      }

      // TASK: missing "Agent Interruption Decision" see original prompt

      $this->currentStep++;
    }
    
    return [
      'success' => $this->isGoalAchieved($goal),
      'steps' => $this->currentStep,
      'interrupted' => $this->interrupted
    ];
  }
  
  private function clarifyGoal(string $goal): void
  {
    $prompt = "Please ask any clarifying questions about this goal: {$goal}";
    $response = $this->ai->ask($prompt);
    $this->memory->append("Clarification: " . $response);
  }
  
  private function createPlan(string $goal): array
  {
    $prompt = "Create a plan to achieve this goal: {$goal}. Return as JSON array of steps.";
    return $this->ai->askStructured($prompt, ['steps' => []]);
  }
  
  private function getNextTask(array $plan): ?array
  {
    return $plan['steps'][$this->currentStep] ?? null;
  }
  
  private function executeTask(array $task): bool
  {
    if( ! isset($this->tools[$task['tool']]))
      throw new \RuntimeException("Unknown tool: {$task['tool']}");
      
    try
    {
      $result = $this->tools[$task['tool']]->execute($task['params'] ?? []);
      $this->memory->append("Task executed: " . json_encode($task) . "\nResult: " . json_encode($result));
      return true;
    }
    catch(\Exception $e)
    {
      $this->memory->append("Task failed: " . $e->getMessage());
      return false;
    }
  }
  
  private function improvePlan(array $plan, array $failedTask): array
  {
    $prompt = "The following task failed: " . json_encode($failedTask) . "\nPlease improve the plan: " . json_encode($plan);
    return $this->ai->askStructured($prompt, ['steps' => []]);
  }
  
  private function isGoalAchieved(string $goal): bool
  {
    $prompt = "Has this goal been achieved? {$goal}\nContext: " . $this->memory->getContent();
    $response = $this->ai->ask($prompt);
    return strtolower(trim($response)) === 'yes';
  }
  
  private function prioritizeTasks(array $plan): array
  {
    $prompt = "Prioritize these tasks based on dependencies and efficiency. Current plan: " . 
              json_encode($plan) . "\nReturn the prioritized plan as JSON with the same structure.";
              
    $schema = [
      'steps' => [
        'type' => 'array',
        'items' => [
          'type' => 'object',
          'properties' => [
            'tool' => ['type' => 'string'],
            'params' => ['type' => 'object'],
            'priority' => ['type' => 'integer'],
            'dependencies' => ['type' => 'array', 'items' => ['type' => 'integer']]
          ]
        ]
      ]
    ];
    
    $prioritizedPlan = $this->ai->askStructured($prompt, $schema);
    
    // Sort steps by priority and dependencies
    usort($prioritizedPlan['steps'], function($a, $b) {
      if( ! empty($a['dependencies']) && in_array($b['priority'], $a['dependencies']))
        return 1;
      if( ! empty($b['dependencies']) && in_array($a['priority'], $b['dependencies']))
        return -1;
      return $a['priority'] <=> $b['priority'];
    });
    
    $this->memory->append("Tasks prioritized: " . json_encode($prioritizedPlan));
    return $prioritizedPlan;
  }
  
  private function analyzePlanForImprovements(array $plan): array
  {
    $prompt = "Analyze this plan for potential improvements:\n" .
              json_encode($plan) . "\nConsider:\n" .
              "1. Can steps be combined or simplified?\n" .
              "2. Are there redundant operations?\n" .
              "3. Can the order be optimized?\n" .
              "Return improved plan as JSON with the same structure.";
              
    $improvedPlan = $this->ai->askStructured($prompt, ['steps' => []]);
    
    if($improvedPlan !== $plan)
      $this->memory->append("Plan improved: " . json_encode($improvedPlan));
      
    return $improvedPlan;
  }
  
  private function verifyTask(array $task): bool
  {
    $prompt = "Verify if this task was successful based on its result and goal context:\n" .
              "Task: " . json_encode($task) . "\n" .
              "Context: " . $this->memory->getContent() . "\n" .
              "Return 'yes' if verified successful, 'no' if failed.";
              
    $response = $this->ai->ask($prompt);
    $verified = strtolower(trim($response)) === 'yes';
    
    $this->memory->append("Task verification: " . ($verified ? "passed" : "failed"));
    return $verified;
  }
  
  public function interrupt(): void
  {
    $this->interrupted = true;
  }
  
  private function canContinue(): bool
  {
    // Check if manually interrupted
    if($this->interrupted)
      return false;
      
    // Check if exceeded max runtime
    if(time() - $this->startTime > $this->maxRuntime)
    {
      $this->memory->append("Agent stopped: Maximum runtime exceeded");
      return false;
    }
    
    // Check if started by scheduler and hit iteration limit
    if(($this->config['schedulerMode'] ?? false) && $this->currentStep >= ($this->config['maxSteps'] ?? 50))
    {
      $this->memory->append("Agent stopped: Maximum steps in scheduler mode reached");
      return false;
    }
    
    return true;
  }
}
