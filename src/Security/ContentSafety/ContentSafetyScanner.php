<?php

declare(strict_types=1);

namespace App\Security\ContentSafety;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ContentSafetyScanner
{
  public function __construct(
    private readonly HttpClientInterface $httpClient,
    private readonly LoggerInterface $logger,
    #[Autowire('%env(string:CONTENT_SAFETY_URL)%')]
    private readonly string $scannerUrl,
    #[Autowire('%env(bool:CONTENT_SAFETY_ENABLED)%')]
    private readonly bool $enabled,
    #[Autowire('%env(float:CONTENT_SAFETY_THRESHOLD)%')]
    private readonly float $threshold,
  ) {
  }

  public function scanImageBlob(string $imageBlob): ContentSafetyResult
  {
    if (!$this->enabled) {
      return ContentSafetyResult::skipped();
    }

    try {
      $response = $this->httpClient->request('POST', $this->scannerUrl.'/scan', [
        'body' => $imageBlob,
        'headers' => ['Content-Type' => 'application/octet-stream'],
        'timeout' => 5,
      ]);

      $data = $response->toArray();

      $nsfwScore = (float) ($data['nsfw_score'] ?? 0.0);
      $isSafe = $nsfwScore < $this->threshold;

      return new ContentSafetyResult(
        safe: $isSafe,
        nsfwScore: $nsfwScore,
        label: $data['label'] ?? 'unknown',
      );
    } catch (\Throwable $e) {
      $this->logger->warning('Content safety scanner unavailable: '.$e->getMessage());

      return ContentSafetyResult::unavailable();
    }
  }

  public function scanImageFile(string $filePath): ContentSafetyResult
  {
    $blob = file_get_contents($filePath);
    if (false === $blob) {
      return ContentSafetyResult::unavailable();
    }

    return $this->scanImageBlob($blob);
  }

  public function scanDataUri(string $dataUri): ContentSafetyResult
  {
    $parts = explode(',', $dataUri, 2);
    if (2 !== count($parts)) {
      return ContentSafetyResult::unavailable();
    }

    $blob = base64_decode($parts[1], true);
    if (false === $blob) {
      return ContentSafetyResult::unavailable();
    }

    return $this->scanImageBlob($blob);
  }
}
