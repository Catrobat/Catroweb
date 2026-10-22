<?php

declare(strict_types=1);

namespace App\Project\Scratch\Converter;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Downloads a public Scratch project (Scratch 2 is translated to 3): metadata (for the project token), project.json and its assets.
 * Every download is size-capped, so a hostile or huge project can't exhaust memory or disk.
 */
class ScratchFetcher
{
  final public const string OLD_SCRATCH_MESSAGE = 'This project was made with an older Scratch version (1.4) and can\'t be converted.';
  private const int MAX_JSON_BYTES = 16 * 1024 * 1024;
  private const int MAX_ASSET_BYTES = 32 * 1024 * 1024;
  private const int MAX_TOTAL_ASSET_BYTES = 128 * 1024 * 1024;
  final public const int MAX_ASSETS = 2_000;
  private const int PARALLEL_DOWNLOADS = 8;

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
      throw new ScratchConversionException('Scratch did not hand out a project token; the project is probably not shared.', ScratchConversionException::NOT_FOUND);
    }

    // Projects not saved since Scratch 3 (2019) still come as Scratch 2 (JSON with "objName", rarely a zip) or Scratch 1.x.
    $body = $this->get('https://projects.scratch.mit.edu/'.$scratch_id.'?token='.rawurlencode($token), self::MAX_JSON_BYTES);
    $project = str_starts_with($body, 'PK') || str_starts_with($body, 'ScratchV0') ? null : json_decode($body, true);
    if (\is_array($project) && isset($project['objName'])) {
      return Scratch2Project::toScratch3($project);
    }

    if (!\is_array($project) || !\is_array($project['targets'] ?? null)) {
      throw new ScratchConversionException(self::OLD_SCRATCH_MESSAGE);
    }

    return $project;
  }

  /**
   * Downloads all assets of a project, a few at a time, each verified against the MD5 that Scratch uses as its name.
   *
   * @param array<string, mixed> $project project.json
   *
   * @return array<string, string> md5ext => bytes
   */
  public function fetchAssets(array $project): array
  {
    $md5exts = [];
    $count = 0;
    foreach ($project['targets'] ?? [] as $target) {
      foreach ([...($target['costumes'] ?? []), ...($target['sounds'] ?? [])] as $asset) {
        if (++$count > self::MAX_ASSETS) {
          throw new ScratchConversionException('The Scratch project has too many costumes and sounds to convert.');
        }

        $md5ext = (string) ($asset['md5ext'] ?? '');
        if (1 !== preg_match('/^[0-9a-f]{32}\.(svg|png|jpg|jpeg|gif|bmp|wav|mp3)$/', $md5ext)) {
          throw new ScratchConversionException('Unexpected asset name: '.$md5ext);
        }

        $md5exts[$md5ext] = true;
      }
    }

    // Shared by all downloads, so the cap holds while they stream, not only once they are in memory.
    $total = 0;
    $assets = [];
    foreach (array_chunk(array_keys($md5exts), self::PARALLEL_DOWNLOADS) as $chunk) {
      $responses = [];
      foreach ($chunk as $md5ext) {
        $responses[$md5ext] = $this->request('https://assets.scratch.mit.edu/internalapi/asset/'.$md5ext.'/get/', self::MAX_ASSET_BYTES, $total);
      }

      foreach ($responses as $md5ext => $response) {
        $body = $this->content($response);
        if (md5($body) !== substr($md5ext, 0, 32)) {
          throw new ScratchConversionException('Asset does not match its checksum: '.$md5ext);
        }

        $assets[$md5ext] = $body;
      }
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

  /** @param int $total bytes downloaded so far by all requests sharing this counter */
  private function request(string $url, int $max_bytes, int &$total = 0): ResponseInterface
  {
    $seen = 0;

    return $this->http_client->request('GET', $url, [
      'timeout' => 15,
      'max_duration' => 60,
      'max_redirects' => 2,
      'headers' => ['User-Agent' => 'Catroweb Scratch converter (+https://share.catrobat.org)'],
      'on_progress' => static function (int $downloaded) use ($max_bytes, &$total, &$seen): void {
        $total += $downloaded - $seen;
        $seen = $downloaded;
        if ($downloaded > $max_bytes || $total > self::MAX_TOTAL_ASSET_BYTES) {
          throw new ScratchConversionException('Download from Scratch is too large.');
        }
      },
    ]);
  }

  private function content(ResponseInterface $response): string
  {
    try {
      $status = $response->getStatusCode();
      if (404 === $status) {
        throw new ScratchConversionException('This Scratch project does not exist or is not shared.', ScratchConversionException::NOT_FOUND);
      }

      if (200 !== $status) {
        throw new ScratchConversionException('Scratch answered with HTTP '.$status.'.', $status >= 500 || 429 === $status ? ScratchConversionException::UPSTREAM : 0);
      }

      return $response->getContent();
    } catch (ScratchConversionException $exception) {
      throw $exception;
    } catch (\Throwable $throwable) {
      // The HTTP client wraps what on_progress throws (a download over its size cap): that one is the project's fault.
      for ($cause = $throwable->getPrevious(); null !== $cause; $cause = $cause->getPrevious()) {
        if ($cause instanceof ScratchConversionException) {
          throw $cause;
        }
      }

      throw new ScratchConversionException('Could not reach Scratch. Please try again later.', ScratchConversionException::UPSTREAM, $throwable);
    }
  }
}
