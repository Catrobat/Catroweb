<?php

declare(strict_types=1);

namespace App\System\Testing;

use App\Project\Scratch\Converter\ScratchConversionException;
use App\Project\Scratch\Converter\ScratchFetcher;

/**
 * Stands in for Scratch in tests: serves the offline project in tests/TestData/ScratchConverter for the ids in its
 * metadata.json. Overrides every public method, so the real fetcher's download internals never run.
 */
class FakeScratchFetcher extends ScratchFetcher
{
  private const string FIXTURES = __DIR__.'/../../../tests/TestData/ScratchConverter/';
  // A file, not memory: Behat and the web server under test run in different processes.
  private const string MODIFIED_OVERRIDES = __DIR__.'/../../../tests/TestData/Cache/fake-scratch-modified.json';

  /** Pretends the project changed on Scratch, so the next open converts it again. */
  public static function setModified(int $scratch_id, string $modified): void
  {
    $overrides = self::modifiedOverrides();
    $overrides[$scratch_id] = $modified;
    @mkdir(\dirname(self::MODIFIED_OVERRIDES), 0o777, true);
    file_put_contents(self::MODIFIED_OVERRIDES, json_encode($overrides, JSON_THROW_ON_ERROR));
  }

  public static function reset(): void
  {
    @unlink(self::MODIFIED_OVERRIDES);
  }

  #[\Override]
  public function fetchMetadata(int $scratch_id): array
  {
    $metadata = self::readJson(self::FIXTURES.'metadata.json')[$scratch_id] ?? null;
    if (!\is_array($metadata)) {
      throw new ScratchConversionException('This Scratch project does not exist or is not shared.', ScratchConversionException::NOT_FOUND);
    }

    $modified = self::modifiedOverrides()[$scratch_id] ?? null;
    if (\is_string($modified)) {
      $metadata['history']['modified'] = $modified;
    }

    return $metadata;
  }

  #[\Override]
  public function fetchProject(int $scratch_id, array $metadata): array
  {
    // "fake_error" in metadata.json makes a project fail like an unconvertible one on Scratch.
    $error = $this->fetchMetadata($scratch_id)['fake_error'] ?? null;
    if (\is_string($error)) {
      throw new ScratchConversionException($error);
    }

    return self::readJson(self::FIXTURES.'project.json');
  }

  #[\Override]
  public function fetchAssets(array $project): array
  {
    $assets = [];
    foreach ($project['targets'] ?? [] as $target) {
      foreach ([...($target['costumes'] ?? []), ...($target['sounds'] ?? [])] as $asset) {
        $md5ext = basename((string) ($asset['md5ext'] ?? ''));
        $assets[$md5ext] ??= (string) file_get_contents(self::FIXTURES.$md5ext);
      }
    }

    return $assets;
  }

  #[\Override]
  public function fetchThumbnail(int $scratch_id): ?string
  {
    return null;
  }

  /**
   * @return array<mixed>
   */
  private static function modifiedOverrides(): array
  {
    return is_file(self::MODIFIED_OVERRIDES) ? self::readJson(self::MODIFIED_OVERRIDES) : [];
  }

  /**
   * @return array<mixed>
   */
  private static function readJson(string $path): array
  {
    $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

    return \is_array($data) ? $data : [];
  }
}
