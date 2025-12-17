<?php

namespace AgentSystem\Tools;

use GuzzleHttp\Client;

class GoogleSearchTool extends Tool
{
  private $client;
  
  public function __construct(array $config = [])
  {
    parent::__construct($config);
    $this->client = new Client();
  }
  
  public function getName(): string
  {
    return 'google_search';
  }
  
  public function getDescription(): array
  {
    return [
      'description' => 'Performs a Google search and returns results',
      'args' => [
        'query' => 'Search query string',
        'num_results' => 'Number of results to return (optional, default 5)'
      ]
    ];
  }
  
  public function execute(array $params): string
  {
    $this->validateParams($params, ['query']);
    
    $numResults = $params['num_results'] ?? 5;
    
    if( ! isset($this->config['apiKey']))
      throw new \RuntimeException('Google Search API key not configured');
      
    try
    {
      $response = $this->client->get('https://www.googleapis.com/customsearch/v1', [
        'query' => [
          'key' => $this->config['apiKey'],
          'cx' => $this->config['search_engine_id'],
          'q' => $params['query'],
          'num' => $numResults
        ]
      ]);
      
      $data = json_decode($response->getBody(), true);
      
      $results = [];
      foreach($data['items'] ?? [] as $item)
      {
        $results[] = [
          'title' => $item['title'],
          'link' => $item['link'],
          'snippet' => $item['snippet']
        ];
      }
      
      return json_encode($results);
    }
    catch(\Exception $e)
    {
      throw new \RuntimeException('Google search failed: ' . $e->getMessage());
    }
  }
}
