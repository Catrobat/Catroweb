<?php

declare(strict_types=1);

namespace App\Storage\Images;

/**
 * Central definition of the shared image variant layout used across
 * studio covers, user avatars, and project screenshots.
 *
 * Produces a fixed set of responsive variants in modern formats
 * (AVIF + WebP) at multiple device-pixel densities so clients can
 * use <picture srcset> to serve minimal bytes per viewport.
 */
final class ImageVariantLayout
{
  public const VARIANT_THUMB = 'thumb';
  public const VARIANT_CARD = 'card';
  public const VARIANT_DETAIL = 'detail';

  public const FORMAT_AVIF = 'avif';
  public const FORMAT_WEBP = 'webp';

  public const DPR_1X = 1;
  public const DPR_2X = 2;

  /**
   * Logical variant name => 1x CSS-pixel width.
   *
   * thumb  ->  96 px (comment/list avatars, studio list cards, sidebar)
   * card   -> 320 px (project tiles, search cards, profile hero retina)
   * detail -> 960 px (project detail screenshot, studio header, featured banner)
   */
  private const WIDTHS_1X = [
    self::VARIANT_THUMB => 96,
    self::VARIANT_CARD => 320,
    self::VARIANT_DETAIL => 960,
  ];

  /**
   * @return list<string>
   */
  public static function variants(): array
  {
    return [self::VARIANT_THUMB, self::VARIANT_CARD, self::VARIANT_DETAIL];
  }

  /**
   * @return list<string>
   */
  public static function formats(): array
  {
    return [self::FORMAT_AVIF, self::FORMAT_WEBP];
  }

  /**
   * @return list<int>
   */
  public static function densities(): array
  {
    return [self::DPR_1X, self::DPR_2X];
  }

  /**
   * Physical pixel width for the given variant + device-pixel ratio.
   */
  public static function widthFor(string $variant, int $dpr): int
  {
    if (!isset(self::WIDTHS_1X[$variant])) {
      throw new \InvalidArgumentException(sprintf('Unknown image variant "%s"', $variant));
    }

    if (self::DPR_1X !== $dpr && self::DPR_2X !== $dpr) {
      throw new \InvalidArgumentException(sprintf('Unsupported device-pixel ratio "%d"', $dpr));
    }

    return self::WIDTHS_1X[$variant] * $dpr;
  }

  /**
   * Filename for a variant: `{basename}-{variant}@{dpr}x.{format}`.
   */
  public static function filename(string $basename, string $variant, int $dpr, string $format): string
  {
    return sprintf('%s-%s@%dx.%s', $basename, $variant, $dpr, $format);
  }

  /**
   * All 12 filenames that make up a complete variant set.
   *
   * @return list<array{variant: string, dpr: int, format: string, filename: string}>
   */
  public static function allVariantFiles(string $basename): array
  {
    $files = [];
    foreach (self::variants() as $variant) {
      foreach (self::densities() as $dpr) {
        foreach (self::formats() as $format) {
          $files[] = [
            'variant' => $variant,
            'dpr' => $dpr,
            'format' => $format,
            'filename' => self::filename($basename, $variant, $dpr, $format),
          ];
        }
      }
    }

    return $files;
  }
}
