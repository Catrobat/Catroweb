<?php

declare(strict_types=1);

namespace App\Project\Scratch\Converter;

use Symfony\Component\Process\Process;

/**
 * Turns Scratch costumes into PNGs Pocket Code can show and keeps Scratch's positioning.
 *
 * Scratch places a costume by its rotation center, Pocket Code by the image center. Padding the
 * canvas so the rotation center lands in the middle keeps every sprite where Scratch drew it.
 */
class AssetNormalizer
{
  // ponytail: costumes render at 1 stage unit per pixel, so they look soft when the 480x360 stage
  // is scaled up on a phone. Render at 2x and halve the sprite size if that matters.
  private const int MAX_SIDE = 2048;

  /**
   * @param array<string, mixed> $costume the costume entry from project.json
   *
   * @return string PNG bytes
   */
  public function costumeToPng(string $bytes, array $costume): string
  {
    $format = strtolower((string) ($costume['dataFormat'] ?? ''));
    $resolution = max(1, (int) ($costume['bitmapResolution'] ?? 1));
    $center_x = (float) ($costume['rotationCenterX'] ?? 0) / ('svg' === $format ? 1 : $resolution);
    $center_y = (float) ($costume['rotationCenterY'] ?? 0) / ('svg' === $format ? 1 : $resolution);

    $image = new \Imagick();
    try {
      if ('svg' === $format) {
        $image->readImageBlob($this->rasterizeSvg($bytes), 'costume.png');
        // Scratch measures the rotation center from the viewBox origin, in viewBox units.
        [$box_x, $box_y, $box_w, $box_h] = self::viewBox($bytes);
        if ($box_w > 0 && $box_h > 0) {
          $center_x = ($center_x - $box_x) * $image->getImageWidth() / $box_w;
          $center_y = ($center_y - $box_y) * $image->getImageHeight() / $box_h;
        }
      } elseif (\in_array($format, ['png', 'jpg', 'jpeg', 'gif', 'bmp'], true)) {
        $image->readImageBlob($bytes);
        $image->setIteratorIndex(0);
        if ($resolution > 1) {
          $image->resizeImage(max(1, intdiv($image->getImageWidth(), $resolution)), max(1, intdiv($image->getImageHeight(), $resolution)), \Imagick::FILTER_LANCZOS, 1);
        }
      } else {
        throw new ScratchConversionException('Unsupported costume format: '.$format);
      }
    } catch (\ImagickException $exception) {
      throw new ScratchConversionException('A costume could not be read.', 0, $exception);
    }

    $width = $image->getImageWidth();
    $height = $image->getImageHeight();
    if ($width > self::MAX_SIDE || $height > self::MAX_SIDE) {
      throw new ScratchConversionException('A costume is too large.');
    }

    // Grow the canvas so (center_x, center_y) becomes its middle; negative offsets shift the image right/down.
    $half_w = (int) ceil(max($center_x, $width - $center_x));
    $half_h = (int) ceil(max($center_y, $height - $center_y));
    $canvas = new \Imagick();
    $canvas->newImage(max(1, 2 * $half_w), max(1, 2 * $half_h), new \ImagickPixel('transparent'), 'png');
    $canvas->compositeImage($image, \Imagick::COMPOSITE_OVER, (int) round($half_w - $center_x), (int) round($half_h - $center_y));
    $canvas->setImageFormat('png');

    return $canvas->getImageBlob();
  }

  /**
   * @param array<string, mixed> $sound the sound entry from project.json
   *
   * @return array{0: string, 1: string} bytes and file extension Android can play
   */
  public function playableSound(string $bytes, array $sound): array
  {
    $format = strtolower((string) ($sound['dataFormat'] ?? ''));
    if ('mp3' === $format) {
      return [$bytes, 'mp3'];
    }

    if ('wav' !== $format) {
      throw new ScratchConversionException('Unsupported sound format: '.$format);
    }

    if (1 === self::wavFormatCode($bytes)) {
      return [$bytes, 'wav'];
    }

    // Scratch stores many sounds as IMA-ADPCM, which Android does not reliably play: transcode to 16-bit PCM.
    $process = new Process(['sox', '-t', 'wav', '-', '-t', 'wav', '-e', 'signed-integer', '-b', '16', '-']);
    $process->setInput($bytes);
    $process->setTimeout(20);
    $process->run();
    if (!$process->isSuccessful() || '' === $process->getOutput()) {
      throw new ScratchConversionException('A sound could not be converted.');
    }

    return [$process->getOutput(), 'wav'];
  }

  /** The audio format code of a RIFF/WAVE file (1 = PCM), or null if there is no fmt chunk. */
  private static function wavFormatCode(string $bytes): ?int
  {
    if (!str_starts_with($bytes, 'RIFF') || 'WAVE' !== substr($bytes, 8, 4)) {
      return null;
    }

    for ($offset = 12; $offset + 8 <= \strlen($bytes);) {
      $size = (int) (unpack('V', substr($bytes, $offset + 4, 4)) ?: [1 => 0])[1];
      if ('fmt ' === substr($bytes, $offset, 4)) {
        return (int) (unpack('v', substr($bytes, $offset + 8, 2)) ?: [1 => 0])[1];
      }

      $offset += 8 + $size + ($size & 1);
    }

    return null;
  }

  /** @return array{0: float, 1: float, 2: float, 3: float} viewBox x, y, width, height (zeros if absent) */
  private static function viewBox(string $svg): array
  {
    if (1 !== preg_match('/<svg\b[^>]*\bviewBox\s*=\s*["\']([^"\']+)["\']/i', $svg, $match)) {
      return [0.0, 0.0, 0.0, 0.0];
    }

    $values = array_map(floatval(...), preg_split('/[\s,]+/', trim($match[1])) ?: []);

    return 4 === \count($values) ? [$values[0], $values[1], $values[2], $values[3]] : [0.0, 0.0, 0.0, 0.0];
  }

  private function rasterizeSvg(string $svg): string
  {
    // rsvg-convert never fetches external resources; no shell, bounded time.
    $process = new Process(['rsvg-convert', '--format=png', '--keep-aspect-ratio']);
    $process->setInput($svg);
    $process->setTimeout(20);
    $process->run();
    if ($process->isSuccessful() && '' !== $process->getOutput()) {
      return $process->getOutput();
    }

    // Scratch's blank costume is a 0x0 SVG, which rsvg refuses; any unrenderable costume becomes invisible.
    $blank = new \Imagick();
    $blank->newImage(1, 1, new \ImagickPixel('transparent'), 'png');

    return $blank->getImageBlob();
  }
}
