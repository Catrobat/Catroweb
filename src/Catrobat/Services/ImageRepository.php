<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use App\Utils\Utils;
use Imagick;
use ImagickException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\UrlHelper;

class ImageRepository
{
  private ?string $example_dir;

  private ?string $featured_dir;

  private ?string $example_path;

  private ?string $featured_path;

  private ?UrlHelper $urlHelper;

  private ?Imagick $imagick = null;

  public function __construct(ParameterBagInterface $parameter_bag, ?UrlHelper $urlHelper = null)
  {
    $example_dir = $parameter_bag->get('catrobat.exampleimage.dir');
    $example_path = $parameter_bag->get('catrobat.exampleimage.path');
    $example_dir = preg_replace('#([^\/]+)$#', '$1/', $example_dir);
    $example_path = preg_replace('#([^\/]+)$#', '$1/', $example_path);

    $featured_dir = $parameter_bag->get('catrobat.featuredimage.dir');
    $featured_path = $parameter_bag->get('catrobat.featuredimage.path');
    $featured_dir = preg_replace('#([^\/]+)$#', '$1/', $featured_dir);
    $featured_path = preg_replace('#([^\/]+)$#', '$1/', $featured_path);

    if (!is_dir($example_dir))
    {
      throw new InvalidStorageDirectoryException($example_dir.' is not a valid directory');
    }

    if (!is_dir($featured_dir))
    {
      throw new InvalidStorageDirectoryException($featured_dir.' is not a valid directory');
    }

    $this->example_dir = $example_dir;
    $this->example_path = $example_path;
    $this->featured_dir = $featured_dir;
    $this->featured_path = $featured_path;
    $this->urlHelper = $urlHelper;
  }

  /**
   * @throws ImagickException
   */
  public function save(File $file, int $id, string $extension, bool $featured): void
  {
    $thumb = $this->getImagick();
    $thumb->readImage($file->__toString());
    if ($featured)
    {
      $filename = $this->featured_dir.$this->generateFileNameFromId($id, $extension, $featured);
      if (file_exists($filename))
      {
        unlink($filename);
      }
      $thumb->writeImage($filename);
      chmod($filename, 0777);
      $thumb->destroy();
    }
    else
    {
      $thumb->cropThumbnailImage(80, 80);
      $filename = $this->example_dir.$this->generateFileNameFromId($id, $extension, $featured);
      if (file_exists($filename))
      {
        unlink($filename);
      }
      $thumb->writeImage($filename);
      chmod($filename, 0777);
      $thumb->destroy();
    }
  }

  public function remove(int $id, string $extension, bool $featured): void
  {
    if ($featured)
    {
      $path = $this->featured_dir.$this->generateFileNameFromId($id, $extension, $featured);
    }
    else
    {
      $path = $this->example_dir.$this->generateFileNameFromId($id, $extension, $featured);
    }
    if (is_file($path))
    {
      unlink($path);
    }
  }

  public function getWebPath(int $id, string $extension, bool $featured): string
  {
    if ($featured)
    {
      $path = $this->featured_path.$this->generateFileNameFromId($id, $extension, $featured);
    }
    else
    {
      $path = $this->example_path.$this->generateFileNameFromId($id, $extension, $featured);
    }

    return $path.Utils::getTimestampParameter($this->example_dir.$this->generateFileNameFromId($id, $extension, $featured));
  }

  public function getAbsoluteWebPath(int $id, string $extension, bool $featured): string
  {
    return $this->urlHelper->getAbsoluteUrl('/').$this->getWebPath($id, $extension, $featured);
  }

  /**
   * @throws ImagickException
   */
  public function getImagick(): Imagick
  {
    if (null == $this->imagick)
    {
      $this->imagick = new Imagick();
    }

    return $this->imagick;
  }

  private function generateFileNameFromId(int $id, string $extension, bool $featured): string
  {
    if ($featured)
    {
      if ('' === $extension)
      {
        return 'featured_'.$id;
      }

      return 'featured_'.$id.'.'.$extension;
    }

    if ('' === $extension)
    {
      return 'example_'.$id;
    }

    return 'example_'.$id.'.'.$extension;
  }
}
