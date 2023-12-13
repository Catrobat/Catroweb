<?php

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

  public function fetchScratchProgramDetails(array $scratch_program_ids): array
  {
    if (0 === count($scratch_program_ids)) {
      return [];
    }

    // number of requests is limited, so the server cannot be abused to run DoS attacks against Scratch server
    if (array_key_exists('max_number_of_total_requests', $this->config)) {
      $max_number_of_total_requests = $this->config['max_number_of_total_requests'];
      $scratch_program_ids = array_slice($scratch_program_ids, 0, $max_number_of_total_requests);
    }

    $promises = function () use ($scratch_program_ids): \Generator {
      /** @var string $scratch_program_id */
      foreach ($scratch_program_ids as $scratch_program_id) {
        $scratch_api_url = 'https://api.scratch.mit.edu/projects/'.$scratch_program_id.'/?format=json';
        yield $this->async_http_client->requestAsync('GET', $scratch_api_url);
      }
    };
    $promises = $promises();

    $max_number_of_concurrent_requests = $this->config['max_number_of_concurrent_requests'] ?? 1;

    $this->scratch_info_data = [];

    (new EachPromise($promises, [
      'concurrency' => $max_number_of_concurrent_requests,
      'fulfilled' => function (ResponseInterface $responses) {
        $data = @json_decode($responses->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);
        if (null != $data && array_key_exists('id', $data) && (int) $data['id'] > 0) {
          $this->scratch_info_data[(int) $data['id']] = $data;
        }
      },
    ]))->promise()->wait();

    return $this->scratch_info_data;
  }
}
