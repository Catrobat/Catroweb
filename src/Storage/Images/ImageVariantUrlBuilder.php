<?php

declare(strict_types=1);

namespace App\Storage\Images;

use App\Storage\FileHelper;
use OpenAPI\Server\Model\ImageVariants as ImageVariantsModel;
use OpenAPI\Server\Model\ImageVariantSet as ImageVariantSetModel;
use Symfony\Component\HttpFoundation\UrlHelper;

/**
 * Builds the {@see ImageVariantsModel} object that gets serialised into
 * API responses. Keeps cache-busting (`?t=` mtime query) consistent with
 * the rest of the codebase via {@see FileHelper::getTimestampParameter()}.
 */
class ImageVariantUrlBuilder
{
  public function __construct(private readonly UrlHelper $urlHelper)
  {
  }

  /**
   * @param string   $storageDir absolute filesystem directory that holds the variants (used only for mtime lookup)
   * @param string   $publicPath public URL path prefix where the same files are served from (e.g. `resources/images/users/`)
   * @param string   $basename   basename shared by every variant file
   * @param int|null $width      optional original-image width, for layout hints
   * @param int|null $height     optional original-image height, for layout hints
   */
  public function build(
    string $storageDir,
    string $publicPath,
    string $basename,
    ?int $width = null,
    ?int $height = null,
    ?string $legacyFallbackPath = null,
  ): ImageVariantsModel {
    $variants = new ImageVariantsModel();
    $variants->setWidth($width);
    $variants->setHeight($height);

    $setters = [
      ImageVariantLayout::VARIANT_THUMB => 'setThumb',
      ImageVariantLayout::VARIANT_CARD => 'setCard',
      ImageVariantLayout::VARIANT_DETAIL => 'setDetail',
    ];

    foreach (ImageVariantLayout::variants() as $variant) {
      $set = $this->buildSet($storageDir, $publicPath, $basename, $variant);
      $variants->{$setters[$variant]}($set);
    }

    // When no variant files exist but a legacy PNG does, use it as webp_1x
    // fallback so the client always has at least one URL to render.
    if (null !== $legacyFallbackPath) {
      $this->applyLegacyFallback($variants, $legacyFallbackPath);
    }

    return $variants;
  }

  private function applyLegacyFallback(ImageVariantsModel $variants, string $fallbackUrl): void
  {
    $setters = [
      'getThumb' => 'setThumb',
      'getCard' => 'setCard',
      'getDetail' => 'setDetail',
    ];

    foreach ($setters as $getter => $setter) {
      $set = $variants->{$getter}();
      if (null === $set) {
        $set = new ImageVariantSetModel();
        $variants->{$setter}($set);
      }

      if (null === $set->getWebp1x() && null === $set->getWebp2x()
          && null === $set->getAvif1x() && null === $set->getAvif2x()) {
        $set->setWebp1x($fallbackUrl);
      }
    }
  }

  private function buildSet(string $storageDir, string $publicPath, string $basename, string $variant): ImageVariantSetModel
  {
    $set = new ImageVariantSetModel();

    foreach (ImageVariantLayout::densities() as $dpr) {
      foreach (ImageVariantLayout::formats() as $format) {
        $url = $this->urlFor($storageDir, $publicPath, $basename, $variant, $dpr, $format);
        $setter = sprintf('set%s%sx', ucfirst($format), $dpr);
        // e.g. setAvif1x, setAvif2x, setWebp1x, setWebp2x
        $set->{$setter}($url);
      }
    }

    return $set;
  }

  private function urlFor(
    string $storageDir,
    string $publicPath,
    string $basename,
    string $variant,
    int $dpr,
    string $format,
  ): ?string {
    $filename = ImageVariantLayout::filename($basename, $variant, $dpr, $format);
    $absoluteFile = rtrim($storageDir, '/').'/'.$filename;

    if (!is_file($absoluteFile)) {
      return null;
    }

    $publicUrl = rtrim($publicPath, '/').'/'.$filename.FileHelper::getTimestampParameter($absoluteFile);

    return $this->urlHelper->getAbsoluteUrl('/'.ltrim($publicUrl, '/'));
  }
}
