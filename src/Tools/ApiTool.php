<?php

namespace AgentSystem\Tools;

class ApiTool extends Tool
{
  public function getName(): string
  {
    return 'api_call';
  }
  
  public function getDescription(): array
  {
    return [
      'description' => 'Makes HTTP requests to APIs with JSON support',
      'args' => [
        'url' => 'API endpoint URL',
        'method' => 'HTTP method (GET, POST, etc.)',
        'data' => 'Optional data to send (will be JSON encoded)',
        'headers' => 'Optional array of headers'
      ]
    ];
  }
  
  public function execute(array $params): array
  {
    $this->validateParams($params, ['url', 'method']);
    
    $ch = curl_init();
    $method = strtoupper($params['method']);
    
    $options = [
      CURLOPT_URL => $params['url'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ];
    
    if( isset($params['data']))
    {
      $jsonData = json_encode($params['data']);
      $options[CURLOPT_POSTFIELDS] = $jsonData;
    }
    
    if( isset($params['headers']))
      $options[CURLOPT_HTTPHEADER] = array_merge(
        $options[CURLOPT_HTTPHEADER], 
        $params['headers']
      );
    
    curl_setopt_array($ch, $options);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if( $error)
      throw new \RuntimeException("API call error: {$error}");
      
    $result = json_decode($response, true);
    
    if( json_last_error() !== JSON_ERROR_NONE)
      throw new \RuntimeException("JSON decode error: " . json_last_error_msg());
      
    return $result;
  }
}
