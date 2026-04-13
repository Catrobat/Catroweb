<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Storage\Images;

use App\Storage\Images\ImageVariantGenerator;
use App\Storage\Images\ImageVariantLayout;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ImageVariantGenerator::class)]
#[RequiresPhpExtension('imagick')]
class ImageVariantGeneratorTest extends TestCase
{
  private string $work_dir;

  private string $source_path;

  #[\Override]
  protected function setUp(): void
  {
    $this->work_dir = BootstrapExtension::$CACHE_DIR.'image_variants/';
    if (is_dir($this->work_dir)) {
      foreach (glob($this->work_dir.'*') ?: [] as $f) {
        @unlink($f);
      }
    } else {
      mkdir($this->work_dir, 0777, true);
    }

    // Build a 2000x2000 source PNG so every variant width requires a real downscale.
    $this->source_path = $this->work_dir.'source.png';
    $imagick = new \Imagick();
    $imagick->newImage(2000, 2000, new \ImagickPixel('#336699'));
    $imagick->setImageFormat('png');
    $imagick->writeImage($this->source_path);
    $imagick->clear();
    $imagick->destroy();
  }

  public function testGenerateProducesWebpVariantsAtExpectedWidths(): void
  {
    $generator = new ImageVariantGenerator();
    $written = $generator->generate($this->source_path, $this->work_dir, 'cover');

    // WebP is always produced — AVIF depends on libheif. At minimum we expect
    // the six WebP files (3 variants × 2 densities).
    $expected_webp = [];
    foreach (ImageVariantLayout::variants() as $variant) {
      foreach (ImageVariantLayout::densities() as $dpr) {
        $expected_webp[] = ImageVariantLayout::filename('cover', $variant, $dpr, ImageVariantLayout::FORMAT_WEBP);
      }
    }

    foreach ($expected_webp as $filename) {
      $path = $this->work_dir.$filename;
      $this->assertFileExists($path, sprintf('Expected variant %s to exist', $filename));
      $this->assertContains($path, $written, sprintf('Expected %s to be reported in the written list', $filename));
    }
  }

  public function testGeneratedWebpWidthsMatchLayout(): void
  {
    $generator = new ImageVariantGenerator();
    $generator->generate($this->source_path, $this->work_dir, 'cover');

    foreach (ImageVariantLayout::variants() as $variant) {
      foreach (ImageVariantLayout::densities() as $dpr) {
        $expected_width = ImageVariantLayout::widthFor($variant, $dpr);
        $filename = ImageVariantLayout::filename('cover', $variant, $dpr, ImageVariantLayout::FORMAT_WEBP);

        $probe = new \Imagick($this->work_dir.$filename);
        $this->assertSame(
          $expected_width,
          $probe->getImageWidth(),
          sprintf('%s should be %d px wide', $filename, $expected_width)
        );
        $probe->clear();
        $probe->destroy();
      }
    }
  }

  public function testRemoveCleansUpEveryVariantFile(): void
  {
    $generator = new ImageVariantGenerator();
    $generator->generate($this->source_path, $this->work_dir, 'cover');

    $generator->remove($this->work_dir, 'cover');

    foreach (ImageVariantLayout::allVariantFiles('cover') as $entry) {
      $this->assertFileDoesNotExist($this->work_dir.$entry['filename']);
    }
  }
}
