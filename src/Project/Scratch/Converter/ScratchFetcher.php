<?php

declare(strict_types=1);

namespace App\Project\Scratch\Converter;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Downloads a public Scratch 3 project: metadata (for the project token), project.json and its assets.
 * Every download is size-capped, so a hostile or huge project can't exhaust memory or disk.
 */
class ScratchFetcher
{
  final public const int MAX_JSON_BYTES = 16 * 1024 * 1024;
  final public const int MAX_ASSET_BYTES = 32 * 1024 * 1024;
  final public const int MAX_TOTAL_ASSET_BYTES = 128 * 1024 * 1024;

  public function __construct(private readonly HttpClientInterface $http_client)
  {
  }

  /**
   * @return array<string, mixed> Scratch API metadata (title, author, project_token, history, ...)
   */
  public function fetchMetadata(int $scratch_id): array
  {
    return $this->getJson('https://api.scratch.mit.edu/projects/'.$scratch_id, 1024 * 1024);
  }

  /**
   * @param array<string, mixed> $metadata
   *
   * @return array<string, mixed> the project.json document
   */
  public function fetchProject(int $scratch_id, array $metadata): array
  {
    $token = $metadata['project_token'] ?? null;
    if (!\is_string($token) || '' === $token) {
      throw new ScratchConversionException('Scratch did not hand out a project token; the project is probably not shared.');
    }

    $project = $this->getJson('https://projects.scratch.mit.edu/'.$scratch_id.'?token='.rawurlencode($token), self::MAX_JSON_BYTES);
    if (!isset($project['targets']) || !\is_array($project['targets'])) {
      throw new ScratchConversionException('Only Scratch 3 projects can be converted.');
    }

    return $project;
  }

  /**
   * Downloads all assets of a project in parallel, each verified against the MD5 that Scratch uses as its name.
   *
   * @param array<string, mixed> $project project.json
   *
   * @return array<string, string> md5ext => bytes
   */
  public function fetchAssets(array $project): array
  {
    $responses = [];
    foreach ($project['targets'] ?? [] as $target) {
      foreach ([...($target['costumes'] ?? []), ...($target['sounds'] ?? [])] as $asset) {
        $md5ext = (string) ($asset['md5ext'] ?? '');
        if (1 !== preg_match('/^[0-9a-f]{32}\.(svg|png|jpg|jpeg|gif|bmp|wav|mp3)$/', $md5ext)) {
          throw new ScratchConversionException('Unexpected asset name: '.$md5ext);
        }

        $responses[$md5ext] ??= $this->request('https://assets.scratch.mit.edu/internalapi/asset/'.$md5ext.'/get/', self::MAX_ASSET_BYTES);
      }
    }

    $assets = [];
    $total = 0;
    foreach ($responses as $md5ext => $response) {
      $body = $this->content($response);
      if (md5($body) !== substr($md5ext, 0, 32)) {
        throw new ScratchConversionException('Asset does not match its checksum: '.$md5ext);
      }

      $total += \strlen($body);
      if ($total > self::MAX_TOTAL_ASSET_BYTES) {
        throw new ScratchConversionException('The project\'s assets are too large to convert.');
      }

      $assets[$md5ext] = $body;
    }

    return $assets;
  }

  /** The project's thumbnail as PNG, or null if Scratch has none. */
  public function fetchThumbnail(int $scratch_id): ?string
  {
    try {
      $bytes = $this->get('https://uploads.scratch.mit.edu/get_image/project/'.$scratch_id.'_480x360.png', 2 * 1024 * 1024);
    } catch (ScratchConversionException) {
      return null;
    }

    return str_starts_with($bytes, "\x89PNG") ? $bytes : null;
  }

  /**
   * @return array<string, mixed>
   */
  private function getJson(string $url, int $max_bytes): array
  {
    try {
      $data = json_decode($this->get($url, $max_bytes), true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException) {
      throw new ScratchConversionException('Scratch returned invalid JSON.');
    }

    if (!\is_array($data)) {
      throw new ScratchConversionException('Scratch returned invalid JSON.');
    }

    return $data;
  }

  private function get(string $url, int $max_bytes): string
  {
    return $this->content($this->request($url, $max_bytes));
  }

  private function request(string $url, int $max_bytes): ResponseInterface
  {
    return $this->http_client->request('GET', $url, [
      'timeout' => 15,
      'max_duration' => 60,
      'max_redirects' => 2,
      'headers' => ['User-Agent' => 'Catroweb Scratch converter (+https://share.catrobat.org)'],
      'on_progress' => static function (int $downloaded) use ($max_bytes): void {
        if ($downloaded > $max_bytes) {
          throw new ScratchConversionException('Download from Scratch is too large.');
        }
      },
    ]);
  }

  private function content(ResponseInterface $response): string
  {
    try {
      if (200 !== $response->getStatusCode()) {
        throw new ScratchConversionException('Scratch answered with HTTP '.$response->getStatusCode().'.');
      }

      return $response->getContent();
    } catch (ScratchConversionException $exception) {
      throw $exception;
    } catch (\Throwable $throwable) {
      throw new ScratchConversionException('Could not download from Scratch: '.$throwable->getMessage(), 0, $throwable);
    }
  }
}
