<?php

namespace AgentSystem\Memory;

class Memory
{
  private $goalDir;
  private $content = '';
  
  public function __construct(string $goalDir)
  {
    $this->goalDir = $goalDir;
    $this->loadContent();
  }
  
  public function append(string $text): void
  {
    $this->content .= $text . "\n";
    file_put_contents($this->goalDir . '/output.txt', $this->content);
  }
  
  public function getContent(): string
  {
    return $this->content;
  }
  
  private function loadContent(): void
  {
    $file = $this->goalDir . '/output.txt';
    if(file_exists($file))
      $this->content = file_get_contents($file);
  }
}
