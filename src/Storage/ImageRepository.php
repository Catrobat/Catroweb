<?php

namespace App\Storage;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\UrlHelper;

class ImageRepository
{
  private readonly ?string $example_dir;

  private readonly ?string $featured_dir;

  private readonly ?string $example_path;

  private readonly ?string $featured_path;

  private ?\Imagick $imagick = null;

  public function __construct(ParameterBagInterface $parameter_bag, private readonly ?UrlHelper $urlHelper = null)
  {
    $example_dir = (string) $parameter_bag->get('catrobat.exampleimage.dir');
    $example_path = (string) $parameter_bag->get('catrobat.exampleimage.path');

    $featured_dir = (string) $parameter_bag->get('catrobat.featuredimage.dir');
    $featured_path = (string) $parameter_bag->get('catrobat.featuredimage.path');

    FileHelper::verifyDirectoryExists($example_dir);
    FileHelper::verifyDirectoryExists($featured_dir);

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

    if (file_exists($filename)) {
      unlink($filename);
    }
    $thumb->writeImage($filename);
    chmod($filename, 0777);
    $thumb->destroy();
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
  }

  public function getWebPath(int|string $id, string $extension, bool $featured): string
  {
    if ($featured) {
      $path = $this->featured_path.$this->generateFileNameFromId($id, $extension, true);
    } else {
      $path = $this->example_path.$this->generateFileNameFromId($id, $extension, false);
    }

    return $path.FileHelper::getTimestampParameter($this->example_dir.$this->generateFileNameFromId($id, $extension, $featured));
  }

  public function getAbsoluteWebPath(int|string $id, string $extension, bool $featured): string
  {
    return $this->urlHelper->getAbsoluteUrl('/').$this->getWebPath($id, $extension, $featured);
  }

  /**
   * @throws \ImagickException
   */
  public function getImagick(): \Imagick
  {
    if (null == $this->imagick) {
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
