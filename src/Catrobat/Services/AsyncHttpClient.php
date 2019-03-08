<?php

namespace App\Catrobat\Services;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\EachPromise;


/**
 * Class AsyncHttpClient
 * @package App\Catrobat\Services
 */
class AsyncHttpClient
{
  /**
   * @var array
   */
  private $config;

  /**
   * @var Client
   */
  private $async_http_client;

  /**
   * @var string[]
   */
  private $scratch_info_data;

  /**
   * AsyncHttpClient constructor.
   *
   * @param array $config
   */
  public function __construct(array $config = [])
  {
    $this->config = $config;
    $this->async_http_client = new Client($config);
    $this->scratch_info_data = null;
  }

  /**
   * @param RemixData[] $scratch_program_ids
   *
   * @return \string[]
   */
  public function fetchScratchProgramDetails(array $scratch_program_ids)
  {
    if (count($scratch_program_ids) == 0)
    {
      return [];
    }

    // number of requests is limited, so the server cannot be abused to run DoS attacks against Scratch server
    if (array_key_exists('max_number_of_total_requests', $this->config))
    {
      $max_number_of_total_requests = $this->config['max_number_of_total_requests'];
      $scratch_program_ids = array_slice($scratch_program_ids, 0, $max_number_of_total_requests);
    }

    $promises = (function () use ($scratch_program_ids) {
      /** @var \GuzzleHttp\Client $http_client */
      foreach ($scratch_program_ids as $scratch_program_id)
      {
        $scratch_api_url = 'https://scratch.mit.edu/api/v1/project/' . $scratch_program_id . '/?format=json';
        yield $this->async_http_client->requestAsync('GET', $scratch_api_url);
      }
    });
    $promises = $promises();

    $max_number_of_concurrent_requests = 1;
    if (array_key_exists('max_number_of_concurrent_requests', $this->config))
    {
      $max_number_of_concurrent_requests = $this->config['max_number_of_concurrent_requests'];
    }

    $this->scratch_info_data = [];

    (new EachPromise($promises, [
      'concurrency' => $max_number_of_concurrent_requests,
      'fulfilled'   => function (ResponseInterface $responses) {
        $data = @json_decode($responses->getBody(), true);
        if ($data != null && array_key_exists('id', $data) && intval($data['id']) > 0)
        {
          $this->scratch_info_data[intval($data['id'])] = $data;
        }
      },
    ]))->promise()->wait();

    return $this->scratch_info_data;
  }
}
