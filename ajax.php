<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use AgentSystem\Agent;
use AgentSystem\Memory\Memory;

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if( ! $input)
{
  echo json_encode(['error' => 'Invalid JSON input']);
  exit;
}

$config = Yaml::parseFile(__DIR__ . '/config.yml');

try
{
  switch($input['action'])
  {
    case 'start_agent':
      if( ! isset($input['llm'], $input['goal']))
        throw new \InvalidArgumentException('Missing required parameters');
        
      if( ! isset($config['llms'][$input['llm']]))
        throw new \InvalidArgumentException('Invalid LLM selected');
        
      // Create timestamped goal directory
      $timestamp = date('Y-m-d_H-i-s');
      $goalDir = __DIR__ . '/goals/' . $timestamp;
      
      // Add suffix if directory already exists
      $suffix = '';
      $counter = 1;
      while(file_exists($goalDir . $suffix))
        $suffix = '_' . $counter++;
        
      $goalDir .= $suffix;
      mkdir($goalDir, 0777, true);
      
      $llmConfig = $config['llms'][$input['llm']];
      $llmClass = 'AgentSystem\\AI\\' . ucfirst($llmConfig['type']) . 'AI';
      
      if( ! class_exists($llmClass))
        throw new \RuntimeException('LLM class not found: ' . $llmClass);
        
      $ai = new $llmClass($llmConfig);
      $memory = new Memory($goalDir);
      
      $agent = new Agent($ai, $config['agent']);
      $agent->setMemory($memory);
      
      // Add tools
      foreach($config['tools'] as $name => $toolConfig)
      {
        $toolClass = 'AgentSystem\\Tools\\' . str_replace('_', '', ucwords($name, '_')) . 'Tool';
        if(class_exists($toolClass))
          $agent->addTool(new $toolClass($toolConfig));
      }
      
      // Start agent in background
      $command = sprintf(
        'php %s/run_agent.php %s %s > /dev/null 2>&1 &',
        __DIR__,
        escapeshellarg($timestamp . $suffix),
        escapeshellarg($input['goal'])
      );
      exec($command);
      
      echo json_encode(['goalId' => $timestamp . $suffix]);
      break;
      
    case 'get_output':
      if( ! isset($input['goalId']))
        throw new \InvalidArgumentException('Missing goalId parameter');
        
      $goalDir = __DIR__ . '/goals/' . $input['goalId'];
      if( ! is_dir($goalDir))
        throw new \InvalidArgumentException('Invalid goalId');
        
      $outputFile = $goalDir . '/output.txt';
      $completedFile = $goalDir . '/completed';
      
      $output = file_exists($outputFile) ? file_get_contents($outputFile) : '';
      $completed = file_exists($completedFile);
      
      echo json_encode([
        'output' => $output,
        'completed' => $completed
      ]);
      break;
      
    default:
      throw new \InvalidArgumentException('Invalid action');
  }
}
catch(\Exception $e)
{
  echo json_encode(['error' => $e->getMessage()]);
}
