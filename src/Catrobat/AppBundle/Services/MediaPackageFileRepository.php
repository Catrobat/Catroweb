<?php

namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException;
use FOS\RestBundle\Controller\Annotations\Unlink;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class MediaPackageFileRepository
{
  private $dir;
  private $path;
  private $filesystem;

  public function __construct($dir, $path)
  {
    $dir = preg_replace('/([^\/]+)$/', '$1/', $dir);
    $path = preg_replace('/([^\/]+)$/', '$1/', $path);

    if (!is_dir($dir))
    {
      throw new InvalidStorageDirectoryException($dir . ' is not a valid directory');
    }

    $this->dir = $dir;
    $this->path = $path;
    $this->filesystem = new Filesystem();
  }

  public function save($file, $id, $extension)
  {
    $file->move($this->dir, $this->generateFileNameFromId($id, $extension));
  }

  public function saveMediaPackageFile(File $file, $id, $extension)
  {
    $this->filesystem->copy($file->getPathname(), $this->dir . $id . '.' . $extension);
  }

  public function remove($id, $extension)
  {
    $path = $this->dir . $this->generateFileNameFromId($id, $extension);
    if (is_file($path))
    {
      unlink($path);
    }
  }

  private function generateFileNameFromId($id, $extension)
  {
    return $id . '.' . $extension;
  }

  public function getWebPath($id, $extension)
  {
    return $this->path . $this->generateFileNameFromId($id, $extension);
  }

  public function getMediaFile($id, $extension)
  {
    return new File($this->dir . $id . '.' . $extension);
  }
}
