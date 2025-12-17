<?php

namespace AgentSystem\Tools;

abstract class Tool
{
  protected $config;
  
  public function __construct(array $config = [])
  {
    $this->config = $config;
  }
  
  abstract public function getName(): string;
  
  abstract public function getDescription(): array;
  
  abstract public function execute(array $params): mixed;
  
  protected function validateParams(array $params, array $required): void
  {
    foreach($required as $param)
      if( ! isset($params[$param]))
        throw new \InvalidArgumentException("Missing required parameter: {$param}");
  }
}
