<?php

declare(strict_types=1);

namespace App\Storage\Images;

use App\Storage\FileHelper;

/**
 * Produces the full AVIF + WebP × 1x/2x × thumb/card/detail variant set
 * for a given source image. Variants are written to disk using an atomic
 * tmp → rename pattern so partially-written files never leak into
 * production.
 *
 * Uses ext-imagick (already required in composer.json). AVIF support
 * depends on ImageMagick being built with libheif; callers should probe
 * {@see self::supportsAvif()} before assuming AVIF output exists.
 */
class ImageVariantGenerator
{
  /** AVIF encoder quality (0–100). Lower = smaller files. */
  private const AVIF_QUALITY = 55;

  /** WebP encoder quality (0–100). */
  private const WEBP_QUALITY = 75;

  public function __construct(
    private readonly int $avifQuality = self::AVIF_QUALITY,
    private readonly int $webpQuality = self::WEBP_QUALITY,
  ) {
  }

  public function supportsAvif(): bool
  {
    return [] !== \Imagick::queryFormats('AVIF');
  }

  /**
   * Generate all variants of $sourcePath into $targetDir using $basename.
   *
   * @return list<string> absolute paths of every file successfully written
   *
   * @throws \ImagickException
   * @throws \RuntimeException
   */
  public function generate(string $sourcePath, string $targetDir, string $basename): array
  {
    if (!is_file($sourcePath)) {
      throw new \RuntimeException(sprintf('Source image not found: %s', $sourcePath));
    }

    FileHelper::ensureDirectoryExists($targetDir);

    $supportsAvif = $this->supportsAvif();
    $written = [];

    foreach (ImageVariantLayout::variants() as $variant) {
      foreach (ImageVariantLayout::densities() as $dpr) {
        $width = ImageVariantLayout::widthFor($variant, $dpr);
        $resized = $this->loadAndResize($sourcePath, $width);

        try {
          foreach (ImageVariantLayout::formats() as $format) {
            if (ImageVariantLayout::FORMAT_AVIF === $format && !$supportsAvif) {
              continue;
            }

            $filename = ImageVariantLayout::filename($basename, $variant, $dpr, $format);
            $finalPath = rtrim($targetDir, '/').'/'.$filename;
            $written[] = $this->writeEncoded($resized, $finalPath, $format);
          }
        } finally {
          $resized->clear();
          $resized->destroy();
        }
      }
    }

    return $written;
  }

  /**
   * Remove every file that belongs to a variant set with the given basename.
   */
  public function remove(string $targetDir, string $basename): void
  {
    foreach (ImageVariantLayout::allVariantFiles($basename) as $file) {
      $path = rtrim($targetDir, '/').'/'.$file['filename'];
      if (is_file($path)) {
        @unlink($path);
      }
    }
  }

  /**
   * @throws \ImagickException
   */
  private function loadAndResize(string $sourcePath, int $targetWidth): \Imagick
  {
    $imagick = new \Imagick();
    $imagick->readImage($sourcePath);
    // Strip metadata (EXIF, ICC) for smaller files and privacy; preserve orientation first.
    $imagick->autoOrient();
    $imagick->stripImage();

    $currentWidth = $imagick->getImageWidth();
    if ($currentWidth > $targetWidth) {
      // Lanczos-quality downscale.
      $imagick->resizeImage($targetWidth, 0, \Imagick::FILTER_LANCZOS, 1);
    }

    return $imagick;
  }

  /**
   * @throws \ImagickException
   * @throws \RuntimeException
   */
  private function writeEncoded(\Imagick $source, string $finalPath, string $format): string
  {
    $clone = clone $source;
    try {
      $clone->setImageFormat($format);

      if (ImageVariantLayout::FORMAT_AVIF === $format) {
        $clone->setImageCompressionQuality($this->avifQuality);
      // AVIF defaults are fine for color; heif container is implicit.
      } elseif (ImageVariantLayout::FORMAT_WEBP === $format) {
        $clone->setImageCompressionQuality($this->webpQuality);
        $clone->setOption('webp:method', '6');
      }

      $tmpPath = $finalPath.'.tmp-'.bin2hex(random_bytes(4));
      if (!$clone->writeImage($tmpPath)) {
        throw new \RuntimeException(sprintf('Failed to write variant: %s', $tmpPath));
      }

      if (!rename($tmpPath, $finalPath)) {
        @unlink($tmpPath);
        throw new \RuntimeException(sprintf('Failed to finalise variant: %s', $finalPath));
      }

      @chmod($finalPath, 0664);

      return $finalPath;
    } finally {
      $clone->clear();
      $clone->destroy();
    }
  }
}
