<?php

namespace App\Catrobat\Services;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ScratchHttpClient
{
  private array $config;

  private string $base_url;

  private HttpClientInterface $client;

  public function __construct(array $config = [], string $base_url = 'https://api.scratch.mit.edu')
  {
    $this->config = $config;
    $this->base_url = $base_url;
    $this->client = HttpClient::create();
  }

  public function getUserData(string $name): ?array
  {
    $path = '/users/'.$name;

    return $this->fetch($path);
  }

  public function getProjectData(int $id): ?array
  {
    $path = '/projects/'.$id;

    return $this->fetch($path);
  }

  public function searchProjects(?string $query, int $offset = 0, int $limit = 40, string $mode = 'trending'): array
  {
    $path = '/search/projects/';
    $fetch_query = ['offset' => $offset, 'limit' => $limit];
    if (null !== $query)
    {
      $fetch_query['q'] = $query;
    }

    return $this->fetch($path, $fetch_query);
  }

  /** @deprecated  */
  public function fetchScratchProgramDetails(array $scratch_program_ids): ?array
  {
    $projects_data = [];
    foreach ($scratch_program_ids as $id)
    {
      try
      {
        $project_data = $this->getProjectData($id);
        if (null !== $project_data)
        {
          $projects_data[$id] = $project_data;
        }
      }
      catch (HttpExceptionInterface | TransportExceptionInterface | DecodingExceptionInterface | ClientExceptionInterface $e)
      {
      }
    }

    return $projects_data;
  }

  /**
   * @throws ClientExceptionInterface
   * @throws DecodingExceptionInterface
   * @throws TransportExceptionInterface
   * @throws RedirectionExceptionInterface
   * @throws ServerExceptionInterface
   *
   * @return array|null Returns an array of the returned data on success, and null when not found (404)
   */
  private function fetch(string $path, array $query = [], string $method = 'GET'): ?array
  {
    $url = $this->base_url.$path;

    do
    {
      $response = $this->client->request($method, $url, $this->config + ['query' => $query]);
      $status_code = $response->getStatusCode();
      if (429 !== $status_code)
      {
        break;
      }
      // sleep a second after TOO_MANY_REQUESTS error, and then try again.
      sleep(1);
    } while (true);

    if (404 === $status_code)
    {
      return null;
    }

    return $response->toArray();
  }
}
