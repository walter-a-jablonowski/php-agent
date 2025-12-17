<?php

if($argc < 3)
{
  echo "Usage: php run_agent.php <goal_id> <goal>\n";
  exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

$goalId = $argv[1];
$goal = $argv[2];

$goalDir = __DIR__ . '/goals/' . $goalId;
if( ! is_dir($goalDir))
{
  echo "Invalid goal directory\n";
  exit(1);
}

try
{
  // Get the agent instance from the session
  $agentFile = $goalDir . '/agent.json';
  if( ! file_exists($agentFile))
  {
    echo "Agent file not found\n";
    exit(1);
  }
  
  $agentData = json_decode(file_get_contents($agentFile), true);
  
  // Run the agent
  $result = $agentData['agent']->achieveGoal($goal);
  
  // Mark as completed
  touch($goalDir . '/completed');
  
  file_put_contents($goalDir . '/output.txt', 
    file_get_contents($goalDir . '/output.txt') . "\n\nGoal " . 
    ($result['success'] ? 'achieved' : 'failed') . 
    " after {$result['steps']} steps."
  );
}
catch(Exception $e)
{
  file_put_contents($goalDir . '/output.txt',
    file_get_contents($goalDir . '/output.txt') . "\n\nError: " . $e->getMessage()
  );
  touch($goalDir . '/completed');
}
