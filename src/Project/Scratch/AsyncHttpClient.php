<?php

declare(strict_types=1);

namespace App\Project\Scratch;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\EachPromise;
use Psr\Http\Message\ResponseInterface;

class AsyncHttpClient
{
  private readonly Client $async_http_client;

  private ?array $scratch_info_data = null;

  public function __construct(private readonly array $config = [])
  {
    $this->async_http_client = new Client($config);
  }

  public function fetchScratchProjectDetails(array $scratch_project_ids): array
  {
    if ([] === $scratch_project_ids) {
      return [];
    }

    // number of requests is limited, so the server cannot be abused to run DoS attacks against Scratch server
    if (array_key_exists('max_number_of_total_requests', $this->config)) {
      $max_number_of_total_requests = $this->config['max_number_of_total_requests'];
      $scratch_project_ids = array_slice($scratch_project_ids, 0, $max_number_of_total_requests);
    }

    $promises = [];
    foreach ($scratch_project_ids as $scratch_project_id) {
      $scratch_api_url = 'https://api.scratch.mit.edu/projects/'.$scratch_project_id.'/?format=json';
      $promises[] = $this->async_http_client->requestAsync('GET', $scratch_api_url);
    }

    $max_number_of_concurrent_requests = $this->config['max_number_of_concurrent_requests'] ?? 1;

    $this->scratch_info_data = [];

    (new EachPromise($promises, [
      'concurrency' => $max_number_of_concurrent_requests,
      'fulfilled' => function (ResponseInterface $responses): void {
        $data = json_decode($responses->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);
        if (null != $data && array_key_exists('id', $data) && (int) $data['id'] > 0) {
          $this->scratch_info_data[(int) $data['id']] = $data;
        }
      },
      'rejected' => function ($reason, $index) {
        // Do nothing
      },
    ]))->promise()->wait();

    return $this->scratch_info_data;
  }
}
