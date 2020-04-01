<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\UrlHelper;

class FeaturedImageRepository
{
  private ?string $dir;

  private ?string $path;

  private ?UrlHelper $urlHelper;

  public function __construct(ParameterBagInterface $parameter_bag, ?UrlHelper $urlHelper = null)
  {
    $dir = $parameter_bag->get('catrobat.featuredimage.dir');
    $path = $parameter_bag->get('catrobat.featuredimage.path');
    $dir = preg_replace('#([^\/]+)$#', '$1/', $dir);
    $path = preg_replace('#([^\/]+)$#', '$1/', $path);

    if (!is_dir($dir))
    {
      throw new InvalidStorageDirectoryException($dir.' is not a valid directory');
    }

    $this->dir = $dir;
    $this->path = $path;
    $this->urlHelper = $urlHelper;
  }

  public function save(File $file, int $id, string $extension): void
  {
    $file->move($this->dir, $this->generateFileNameFromId($id, $extension));
  }

  public function remove(int $id, string $extension): void
  {
    $path = $this->dir.$this->generateFileNameFromId($id, $extension);
    if (is_file($path))
    {
      unlink($path);
    }
  }

  public function getWebPath(int $id, string $extension): string
  {
    return $this->path.$this->generateFileNameFromId($id, $extension);
  }

  public function getAbsoluteWWebPath(int $id, string $extension): string
  {
    return $this->urlHelper->getAbsoluteUrl('/').$this->path.$this->generateFileNameFromId($id, $extension);
  }

  private function generateFileNameFromId(int $id, string $extension): string
  {
    if ('' === $extension)
    {
      return 'featured_'.$id;
    }

    return 'featured_'.$id.'.'.$extension;
  }
}
