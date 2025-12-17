<?php

namespace AgentSystem\AI;

use GuzzleHttp\Client;

class GeminiAI extends AI
{
  private $client;
  private $apiKey;
  
  public function __construct(array $config, $memory = null)
  {
    parent::__construct($config, $memory);
    $this->apiKey = $config['apiKey'] ?? '';
    $this->client = new Client();
    
    if( ! $this->apiKey)
      throw new \RuntimeException('Gemini API key not configured');
  }
  
  public function ask(string $prompt): string
  {
    try 
    {
      $context = $this->getMemoryContext();
      if($context)
        $prompt = "Previous context:\n{$context}\n\nNew prompt:\n{$prompt}";
      
      $response = $this->client->post(
        'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=' . $this->apiKey,
        [
          'json' => [
            'contents' => [
              [
                'parts' => [
                  [
                    'text' => $prompt
                  ]
                ]
              ]
            ]
          ]
        ]
      );
      
      $result = json_decode($response->getBody(), true);
      
      if(isset($result['candidates'][0]['content']['parts'][0]['text']))
        return $result['candidates'][0]['content']['parts'][0]['text'];
        
      throw new \RuntimeException('Unexpected API response format');
    }
    catch(\Exception $e)
    {
      throw new \RuntimeException('Gemini API request failed: ' . $e->getMessage());
    }
  }
  
  public function askStructured(string $prompt, array $format): array
  {
    $structuredPrompt = $prompt . "\n\nPlease format your response as a JSON object with the following structure:\n" . 
                       json_encode($format, JSON_PRETTY_PRINT);
    
    $response = $this->ask($structuredPrompt);
    
    try
    {
      $data = json_decode($response, true);
      if(json_last_error() !== JSON_ERROR_NONE)
        throw new \RuntimeException('Invalid JSON response');
        
      return $data;
    }
    catch(\Exception $e)
    {
      throw new \RuntimeException('Failed to parse structured response: ' . $e->getMessage());
    }
  }
}
