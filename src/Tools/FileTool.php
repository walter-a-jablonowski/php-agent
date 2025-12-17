<?php

namespace AgentSystem\Tools;

class FileTool extends Tool
{
  private string $goalsDir;
  
  public function __construct(array $config = [])
  {
    parent::__construct($config);
    $this->goalsDir = realpath(__DIR__ . '/../../goals');
  }
  
  public function getName(): string
  {
    return 'file_ops';
  }
  
  public function getDescription(): array
  {
    return [
      'description' => 'Performs file operations within the goals directory',
      'args' => [
        'operation' => 'Operation to perform (read/write/list/delete)',
        'path' => 'Relative path within goals directory',
        'content' => 'Content to write (for write operation)'
      ]
    ];
  }
  
  public function execute(array $params): mixed
  {
    $this->validateParams($params, ['operation', 'path']);
    
    $safePath = $this->getSafePath($params['path']);
    
    return match($params['operation'])
    {
      'read' => $this->readFile($safePath),
      'write' => $this->writeFile($safePath, $params['content'] ?? ''),
      'list' => $this->listDirectory($safePath),
      'delete' => $this->deleteFile($safePath),
      default => throw new \InvalidArgumentException("Invalid operation: {$params['operation']}")
    };
  }
  
  private function getSafePath(string $path): string
  {
    $absolutePath = realpath($this->goalsDir . '/' . $path) 
      ?: $this->goalsDir . '/' . $path;
      
    if( ! str_starts_with($absolutePath, $this->goalsDir))
      throw new \RuntimeException('Access denied: Path outside goals directory');
      
    return $absolutePath;
  }
  
  private function readFile(string $path): string
  {
    if( ! file_exists($path))
      throw new \RuntimeException("File not found: {$path}");
      
    return file_get_contents($path);
  }
  
  private function writeFile(string $path, string $content): void
  {
    $dir = dirname($path);
    if( ! file_exists($dir))
      mkdir($dir, 0777, true);
      
    file_put_contents($path, $content);
  }
  
  private function listDirectory(string $path): array
  {
    if( ! is_dir($path))
      throw new \RuntimeException("Not a directory: {$path}");
      
    return scandir($path);
  }
  
  private function deleteFile(string $path): void
  {
    if( ! file_exists($path))
      throw new \RuntimeException("File not found: {$path}");
      
    unlink($path);
  }
}
