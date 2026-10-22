<?php

declare(strict_types=1);

namespace App\Project\Scratch\Converter;

use Symfony\Component\Process\Process;

/**
 * Turns Scratch costumes into PNGs Pocket Code can show and keeps Scratch's positioning.
 *
 * Scratch places a costume by its rotation center, Pocket Code by the image center. Padding the
 * canvas so the rotation center lands in the middle keeps every sprite where Scratch drew it.
 *
 * Costumes come from untrusted projects, so sizes are checked before anything is decoded or rendered.
 */
class AssetNormalizer
{
  // ponytail: costumes render at 1 stage unit per pixel, so they look soft when the 480x360 stage
  // is scaled up on a phone. Render at 2x and halve the sprite size if that matters.
  private const int MAX_SIDE = 2048;
  private const int MAX_SOUND_BYTES = 32 * 1024 * 1024;
  private const int TOOL_TIMEOUT = 20;
  private const int TOOL_MISSING = 127;

  /**
   * @param array<string, mixed> $costume  the costume entry from project.json
   * @param float                $deadline microtime(true) after which the conversion gives up
   *
   * @return string PNG bytes
   */
  public function costumeToPng(string $bytes, array $costume, float $deadline): string
  {
    \Imagick::setResourceLimit(\Imagick::RESOURCETYPE_MEMORY, 256 * 1024 * 1024);
    \Imagick::setResourceLimit(\Imagick::RESOURCETYPE_AREA, 4 * self::MAX_SIDE * self::MAX_SIDE);

    $format = strtolower((string) ($costume['dataFormat'] ?? ''));
    $resolution = max(1, (int) ($costume['bitmapResolution'] ?? 1));
    $center_x = (float) ($costume['rotationCenterX'] ?? 0);
    $center_y = (float) ($costume['rotationCenterY'] ?? 0);

    try {
      $image = new \Imagick();
      if ('svg' === $format) {
        $size = self::svgSize($bytes);
        if (null === $size) {
          return self::blank(); // Scratch's blank costume is a 0x0 SVG
        }

        $image->readImageBlob($this->rasterizeSvg($bytes, $size, $deadline), 'costume.png');
        // Scratch measures the rotation center from the viewBox origin, in viewBox units.
        [$box_x, $box_y, $box_w, $box_h] = self::viewBox($bytes) ?? [0.0, 0.0, (float) $size[0], (float) $size[1]];
        $center_x = ($center_x - $box_x) * $image->getImageWidth() / max(1e-9, $box_w);
        $center_y = ($center_y - $box_y) * $image->getImageHeight() / max(1e-9, $box_h);
      } elseif (\in_array($format, ['png', 'jpg', 'jpeg', 'gif', 'bmp'], true)) {
        $probe = new \Imagick();
        $probe->pingImageBlob($bytes);
        if ($probe->getImageWidth() > self::MAX_SIDE * $resolution || $probe->getImageHeight() > self::MAX_SIDE * $resolution) {
          throw new ScratchConversionException('A costume is too large.');
        }

        $image->readImageBlob($bytes);
        $image->setIteratorIndex(0);
        if ($resolution > 1) {
          $image->resizeImage(max(1, intdiv($image->getImageWidth(), $resolution)), max(1, intdiv($image->getImageHeight(), $resolution)), \Imagick::FILTER_LANCZOS, 1);
        }

        $center_x /= $resolution;
        $center_y /= $resolution;
      } else {
        throw new ScratchConversionException('Unsupported costume format: '.$format);
      }

      $width = $image->getImageWidth();
      $height = $image->getImageHeight();

      // Grow the canvas so (center_x, center_y) becomes its middle. An absurd center is capped: such a
      // costume then sits off its Scratch position instead of allocating a huge canvas.
      $half_w = min(self::MAX_SIDE, (int) ceil(max($center_x, $width - $center_x)));
      $half_h = min(self::MAX_SIDE, (int) ceil(max($center_y, $height - $center_y)));
      $canvas = new \Imagick();
      $canvas->newImage(max(1, 2 * $half_w), max(1, 2 * $half_h), new \ImagickPixel('transparent'), 'png');
      $canvas->compositeImage($image, \Imagick::COMPOSITE_OVER, (int) round($half_w - $center_x), (int) round($half_h - $center_y));
      // Always RGBA: Pocket Code (libGDX) loads an 8-bit grayscale PNG as alpha only, so black became transparent.
      $canvas->setImageFormat('png32');

      return $canvas->getImageBlob();
    } catch (\ImagickException $exception) {
      throw new ScratchConversionException('A costume could not be read.', 0, $exception);
    }
  }

  /**
   * @param array<string, mixed> $sound the sound entry from project.json
   *
   * @return array{0: string, 1: string} bytes and file extension Android can play
   */
  public function playableSound(string $bytes, array $sound, float $deadline): array
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
    $output = self::run(['sox', '-t', 'wav', '-', '-t', 'wav', '-e', 'signed-integer', '-b', '16', '-'], $bytes, $deadline);
    if (null === $output || '' === $output) {
      throw new ScratchConversionException('A sound could not be converted.');
    }

    if (\strlen($output) > self::MAX_SOUND_BYTES) {
      throw new ScratchConversionException('A sound is too long to convert.');
    }

    return [$output, 'wav'];
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

  /** @return array{0: float, 1: float, 2: float, 3: float}|null viewBox x, y, width, height */
  private static function viewBox(string $svg): ?array
  {
    if (1 !== preg_match('/<svg\b[^>]*\bviewBox\s*=\s*["\']([^"\']+)["\']/i', $svg, $match)) {
      return null;
    }

    $values = array_map(floatval(...), preg_split('/[\s,]+/', trim($match[1])) ?: []);

    return 4 === \count($values) && $values[2] > 0 && $values[3] > 0 ? [$values[0], $values[1], $values[2], $values[3]] : null;
  }

  /**
   * The pixel size rsvg will render: the svg's width/height in px (or unitless), else its viewBox.
   *
   * @return array{0: int, 1: int}|null null for an empty costume
   */
  private static function svgSize(string $svg): ?array
  {
    $attribute = static function (string $name) use ($svg): ?float {
      if (1 !== preg_match('/<svg\b[^>]*\b'.$name.'\s*=\s*["\']\s*([0-9.]+)\s*(px)?\s*["\']/i', $svg, $match)) {
        return null;
      }

      return (float) $match[1];
    };

    $box = self::viewBox($svg);
    $width = $attribute('width') ?? $box[2] ?? null;
    $height = $attribute('height') ?? $box[3] ?? null;
    if (null === $width || null === $height || $width < 0.5 || $height < 0.5) {
      return null;
    }

    if ($width > self::MAX_SIDE || $height > self::MAX_SIDE) {
      throw new ScratchConversionException('A costume is too large.');
    }

    return [(int) ceil($width), (int) ceil($height)];
  }

  /** @param array{0: int, 1: int} $size */
  private function rasterizeSvg(string $svg, array $size, float $deadline): string
  {
    // From stdin, rsvg-convert loads no external files or URLs. The explicit size bounds its memory.
    $png = self::run(['rsvg-convert', '--format=png', '--width='.$size[0], '--height='.$size[1]], $svg, $deadline);

    return null === $png || '' === $png ? self::blank() : $png; // an unrenderable costume becomes invisible
  }

  /**
   * @param list<string> $command
   *
   * @return string|null output, null if the tool failed on this input
   */
  private static function run(array $command, string $input, float $deadline): ?string
  {
    $remaining = $deadline - microtime(true);
    if ($remaining <= 0) {
      throw new ScratchConversionException('The Scratch project takes too long to convert.');
    }

    $process = new Process($command);
    $process->setInput($input);
    $process->setTimeout(min(self::TOOL_TIMEOUT, $remaining));
    try {
      $process->run();
    } catch (\Symfony\Component\Process\Exception\ProcessTimedOutException) {
      throw new ScratchConversionException('The Scratch project takes too long to convert.');
    }

    if (self::TOOL_MISSING === $process->getExitCode()) {
      throw new \RuntimeException($command[0].' is not installed; the Scratch converter needs it.');
    }

    return $process->isSuccessful() ? $process->getOutput() : null;
  }

  private static function blank(): string
  {
    $blank = new \Imagick();
    $blank->newImage(1, 1, new \ImagickPixel('transparent'), 'png32');

    return $blank->getImageBlob();
  }
}
