<?php

declare(strict_types=1);

namespace App\Storage;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

class ContentAddressableStore
{
  private readonly Filesystem $filesystem;

  public function __construct(
    #[Autowire('%catrobat.file.assets.dir%')]
    private readonly string $assetsDir,
  ) {
    $this->filesystem = new Filesystem();
  }

  public function hashFile(string $filePath): string
  {
    $hash = hash_file('sha256', $filePath);
    if (false === $hash) {
      throw new \RuntimeException("Failed to hash file: {$filePath}");
    }

    return $hash;
  }

  /**
   * Store a file. Returns the storage path relative to assets dir.
   * If the file already exists (same hash), this is a no-op.
   */
  public function store(string $sourceFilePath, string $hash): string
  {
    $relativePath = $this->buildRelativePath($hash);
    $absolutePath = $this->assetsDir.$relativePath;

    if (file_exists($absolutePath)) {
      if (filesize($absolutePath) === filesize($sourceFilePath)) {
        return $relativePath;
      }

      throw new \RuntimeException("Hash collision or corruption detected for hash: {$hash}");
    }

    $dir = dirname($absolutePath);
    if (!is_dir($dir)) {
      $this->filesystem->mkdir($dir, 0o755);
    }

    // Atomic write: copy to temp file, then rename
    $tempPath = $absolutePath.'.tmp.'.bin2hex(random_bytes(4));
    $this->filesystem->copy($sourceFilePath, $tempPath);
    rename($tempPath, $absolutePath);

    return $relativePath;
  }

  public function getAbsolutePath(string $hash): ?string
  {
    $path = $this->assetsDir.$this->buildRelativePath($hash);

    return file_exists($path) ? $path : null;
  }

  public function getAbsolutePathFromRelative(string $relativePath): string
  {
    return $this->assetsDir.$relativePath;
  }

  public function delete(string $hash): bool
  {
    $path = $this->assetsDir.$this->buildRelativePath($hash);
    if (!file_exists($path)) {
      return false;
    }

    unlink($path);

    // Clean up empty parent directories (2 levels)
    $parentDir = dirname($path);
    if (is_dir($parentDir) && $this->isDirectoryEmpty($parentDir)) {
      rmdir($parentDir);
      $grandParentDir = dirname($parentDir);
      if (is_dir($grandParentDir) && $this->isDirectoryEmpty($grandParentDir)) {
        rmdir($grandParentDir);
      }
    }

    return true;
  }

  public function exists(string $hash): bool
  {
    return file_exists($this->assetsDir.$this->buildRelativePath($hash));
  }

  /**
   * Build relative path: {hash[0:2]}/{hash[2:4]}/{hash}.
   */
  private function buildRelativePath(string $hash): string
  {
    return substr($hash, 0, 2).'/'.substr($hash, 2, 2).'/'.$hash;
  }

  private function isDirectoryEmpty(string $dir): bool
  {
    $handle = opendir($dir);
    if (false === $handle) {
      return false;
    }

    while (false !== ($entry = readdir($handle))) {
      if ('.' !== $entry && '..' !== $entry) {
        closedir($handle);

        return false;
      }
    }

    closedir($handle);

    return true;
  }
}
