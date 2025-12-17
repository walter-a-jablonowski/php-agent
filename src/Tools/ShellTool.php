<?php

namespace AgentSystem\Tools;

class ShellTool extends Tool
{
  public function getName(): string
  {
    return 'shell_command';
  }
  
  public function getDescription(): array
  {
    return [
      'description' => 'Executes shell commands with safety restrictions',
      'args' => [
        'command' => 'Command to execute',
        'args' => 'Array of command arguments',
        'cwd' => 'Optional working directory'
      ]
    ];
  }
  
  public function execute(array $params): array
  {
    $this->validateParams($params, ['command', 'args']);
    
    // Validate command is allowed
    if( ! $this->isCommandAllowed($params['command']))
      throw new \RuntimeException("Command not allowed: {$params['command']}");
    
    $command = escapeshellcmd($params['command']);
    $args = array_map('escapeshellarg', $params['args']);
    $fullCommand = $command . ' ' . implode(' ', $args);
    
    $cwd = isset($params['cwd']) ? $this->validateWorkingDir($params['cwd']) : null;
    
    $output = [];
    $returnCode = 0;
    
    exec($fullCommand, $output, $returnCode);
    
    return [
      'output' => $output,
      'return_code' => $returnCode
    ];
  }
  
  private function isCommandAllowed(string $command): bool
  {
    // Add allowed commands here
    $allowedCommands = [
      'ls', 'dir', 'echo', 'cat', 'type',
      'php', 'composer'
    ];
    
    return in_array($command, $allowedCommands);
  }
  
  private function validateWorkingDir(string $dir): string
  {
    $realPath = realpath($dir);
    
    if( ! $realPath || ! is_dir($realPath))
      throw new \RuntimeException("Invalid working directory: {$dir}");
      
    return $realPath;
  }
}
