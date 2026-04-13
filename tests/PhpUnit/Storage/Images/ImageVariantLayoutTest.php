<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Storage\Images;

use App\Storage\Images\ImageVariantLayout;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ImageVariantLayout::class)]
class ImageVariantLayoutTest extends TestCase
{
  public function testVariantWidthsMatchDesignTargets(): void
  {
    // 1x widths mirror the issue-6628 design targets (96/320/960 CSS px).
    $this->assertSame(96, ImageVariantLayout::widthFor(ImageVariantLayout::VARIANT_THUMB, 1));
    $this->assertSame(320, ImageVariantLayout::widthFor(ImageVariantLayout::VARIANT_CARD, 1));
    $this->assertSame(960, ImageVariantLayout::widthFor(ImageVariantLayout::VARIANT_DETAIL, 1));

    // 2x = 1x doubled (retina).
    $this->assertSame(192, ImageVariantLayout::widthFor(ImageVariantLayout::VARIANT_THUMB, 2));
    $this->assertSame(640, ImageVariantLayout::widthFor(ImageVariantLayout::VARIANT_CARD, 2));
    $this->assertSame(1920, ImageVariantLayout::widthFor(ImageVariantLayout::VARIANT_DETAIL, 2));
  }

  public function testUnknownVariantThrows(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    ImageVariantLayout::widthFor('hero', 1);
  }

  public function testUnsupportedDensityThrows(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    ImageVariantLayout::widthFor(ImageVariantLayout::VARIANT_THUMB, 3);
  }

  public function testFilenameSchemeIsStableAcrossFormatsAndDensities(): void
  {
    $this->assertSame('cover-thumb@1x.avif', ImageVariantLayout::filename('cover', 'thumb', 1, 'avif'));
    $this->assertSame('cover-detail@2x.webp', ImageVariantLayout::filename('cover', 'detail', 2, 'webp'));
  }

  public function testAllVariantFilesListsTheFullTwelveFileSet(): void
  {
    $files = ImageVariantLayout::allVariantFiles('avatar-key');

    // 3 variants × 2 densities × 2 formats = 12 files.
    $this->assertCount(12, $files);

    $filenames = array_column($files, 'filename');
    $expected = [
      'avatar-key-thumb@1x.avif',
      'avatar-key-thumb@1x.webp',
      'avatar-key-thumb@2x.avif',
      'avatar-key-thumb@2x.webp',
      'avatar-key-card@1x.avif',
      'avatar-key-card@1x.webp',
      'avatar-key-card@2x.avif',
      'avatar-key-card@2x.webp',
      'avatar-key-detail@1x.avif',
      'avatar-key-detail@1x.webp',
      'avatar-key-detail@2x.avif',
      'avatar-key-detail@2x.webp',
    ];
    sort($filenames);
    sort($expected);
    $this->assertSame($expected, $filenames);
  }
}
