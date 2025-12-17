<?php

namespace AgentSystem\AI;

abstract class AI
{
  protected $config;
  protected $memory;
  
  public function __construct(array $config, $memory = null)
  {
    $this->config = $config;
    $this->memory = $memory;
  }

  abstract public function ask(string $prompt): string;
  
  abstract public function askStructured(string $prompt, array $format): array;
  
  protected function getMemoryContext(): ?string
  {
    if($this->memory === null)
      return null;
      
    return $this->memory->getContent();
  }
  
  public function setMemory($memory): void
  {
    $this->memory = $memory;
  }
}
