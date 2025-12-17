<?php

namespace AgentSystem\Tools;

class WebReaderTool extends Tool
{
  public function getName(): string
  {
    return 'web_reader';
  }
  
  public function getDescription(): array
  {
    return [
      'description' => 'Reads content from a web page using cURL',
      'args' => [
        'url' => 'The URL to read from',
        'headers' => 'Optional array of headers to send'
      ]
    ];
  }
  
  public function execute(array $params): string
  {
    $this->validateParams($params, ['url']);
    
    $ch = curl_init();
    
    curl_setopt_array($ch, [
      CURLOPT_URL => $params['url'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_MAXREDIRS => 5,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]);
    
    if( isset($params['headers']))
      curl_setopt($ch, CURLOPT_HTTPHEADER, $params['headers']);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if( $error)
      throw new \RuntimeException("cURL error: {$error}");
      
    return $response;
  }
}
