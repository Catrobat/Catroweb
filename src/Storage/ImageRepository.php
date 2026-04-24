<?php

declare(strict_types=1);

namespace App\Storage;

use App\Storage\Images\ImageVariantGenerator;
use App\Storage\Images\ImageVariantUrlBuilder;
use OpenAPI\Server\Model\ImageVariants;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\UrlHelper;

class ImageRepository
{
  private readonly string $example_dir;

  private readonly string $featured_dir;

  private readonly string $example_path;

  private readonly string $featured_path;

  private ?\Imagick $imagick = null;

  /**
   * @throws \Exception
   */
  public function __construct(
    ParameterBagInterface $parameter_bag,
    private readonly ?UrlHelper $urlHelper = null,
    private readonly ?ImageVariantGenerator $image_variant_generator = null,
    private readonly ?ImageVariantUrlBuilder $image_variant_url_builder = null,
  ) {
    /** @var string $example_dir */
    $example_dir = $parameter_bag->get('catrobat.exampleimage.dir');
    /** @var string $example_path */
    $example_path = $parameter_bag->get('catrobat.exampleimage.path');

    /** @var string $featured_dir */
    $featured_dir = $parameter_bag->get('catrobat.featuredimage.dir');
    /** @var string $featured_path */
    $featured_path = $parameter_bag->get('catrobat.featuredimage.path');

    FileHelper::ensureDirectoryExists($example_dir);
    FileHelper::ensureDirectoryExists($featured_dir);

    $this->example_dir = $example_dir;
    $this->example_path = $example_path;
    $this->featured_dir = $featured_dir;
    $this->featured_path = $featured_path;
  }

  /**
   * @throws \ImagickException
   */
  public function save(File $file, int|string $id, string $extension, bool $featured): void
  {
    $thumb = $this->getImagick();
    $thumb->readImage($file->__toString());
    if ($featured) {
      $filename = $this->featured_dir.$this->generateFileNameFromId($id, $extension, true);
    } else {
      $thumb->cropThumbnailImage(80, 80);
      $filename = $this->example_dir.$this->generateFileNameFromId($id, $extension, false);
    }

    $dir = dirname($filename);
    if (!is_dir($dir)) {
      mkdir($dir, 0775, true);
    }

    if (!is_writable($dir)) {
      throw new \RuntimeException(sprintf('Directory "%s" is not writable. Check file ownership and permissions.', $dir));
    }

    if (file_exists($filename)) {
      unlink($filename);
    }

    $thumb->writeImage($filename);
    chmod($filename, 0664);
    $thumb->destroy();

    // Featured banners get the full responsive variant set alongside the
    // legacy file (example crops stay at 80x80 — they're already sized for
    // their only render context).
    if ($featured && $this->image_variant_generator instanceof ImageVariantGenerator) {
      $this->image_variant_generator->generate(
        $filename,
        $this->featured_dir,
        $this->getVariantBasename($id, true),
      );
    }
  }

  public function remove(int|string $id, string $extension, bool $featured): void
  {
    if ($featured) {
      $path = $this->featured_dir.$this->generateFileNameFromId($id, $extension, true);
    } else {
      $path = $this->example_dir.$this->generateFileNameFromId($id, $extension, false);
    }

    if (is_file($path)) {
      unlink($path);
    }

    if ($featured && $this->image_variant_generator instanceof ImageVariantGenerator) {
      $this->image_variant_generator->remove($this->featured_dir, $this->getVariantBasename($id, true));
    }
  }

  public function getFeaturedVariants(int|string $id): ?ImageVariants
  {
    if (!$this->image_variant_url_builder instanceof ImageVariantUrlBuilder) {
      return null;
    }

    return $this->image_variant_url_builder->build(
      $this->featured_dir,
      $this->featured_path,
      $this->getVariantBasename($id, true),
    );
  }

  private function getVariantBasename(int|string $id, bool $featured): string
  {
    return ($featured ? 'featured_' : 'example_').$id;
  }

  public function getWebPath(int|string $id, string $extension, bool $featured): string
  {
    if ($featured) {
      $path = $this->featured_path.$this->generateFileNameFromId($id, $extension, true);
    } else {
      $path = $this->example_path.$this->generateFileNameFromId($id, $extension, false);
    }

    $dir = $featured ? $this->featured_dir : $this->example_dir;

    return $path.FileHelper::getTimestampParameter($dir.$this->generateFileNameFromId($id, $extension, $featured));
  }

  public function getAbsoluteWebPath(int|string $id, string $extension, bool $featured): string
  {
    return $this->urlHelper->getAbsoluteUrl('/').$this->getWebPath($id, $extension, $featured);
  }

  public function getImagick(): \Imagick
  {
    if (null === $this->imagick) {
      $this->imagick = new \Imagick();
    }

    return $this->imagick;
  }

  private function generateFileNameFromId(int|string $id, string $extension, bool $featured): string
  {
    if ($featured) {
      if ('' === $extension) {
        return 'featured_'.$id;
      }

      return 'featured_'.$id.'.'.$extension;
    }

    if ('' === $extension) {
      return 'example_'.$id;
    }

    return 'example_'.$id.'.'.$extension;
  }
}
