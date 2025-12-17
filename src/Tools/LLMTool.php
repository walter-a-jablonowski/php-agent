<?php

namespace AgentSystem\Tools;

class LLMTool extends Tool
{
  private $ai;
  
  public function __construct(array $config = [])
  {
    parent::__construct($config);
    $this->ai = $config['ai'] ?? throw new \InvalidArgumentException('AI instance required');
  }
  
  public function getName(): string
  {
    return 'llm_interaction';
  }
  
  public function getDescription(): array
  {
    return [
      'description' => 'Interacts with the configured LLM',
      'args' => [
        'prompt' => 'The prompt to send to the LLM',
        'format' => 'Optional output format (text/json)',
        'schema' => 'JSON schema when format is json'
      ]
    ];
  }
  
  public function execute(array $params): mixed
  {
    $this->validateParams($params, ['prompt']);
    
    $format = $params['format'] ?? 'text';
    
    if( $format === 'json')
    {
      if( ! isset($params['schema']))
        throw new \InvalidArgumentException('Schema required for JSON format');
        
      return $this->ai->getStructuredResponse(
        $params['prompt'],
        $params['schema']
      );
    }
    
    return $this->ai->getResponse($params['prompt']);
  }
}
