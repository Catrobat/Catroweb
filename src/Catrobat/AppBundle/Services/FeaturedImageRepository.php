<?php

namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException;
use FOS\RestBundle\Controller\Annotations\Unlink;

class FeaturedImageRepository
{
  private $dir;
  private $path;

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
  }

  public function save($file, $id, $extension)
  {
    $file->move($this->dir, $this->generateFileNameFromId($id, $extension));
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
    return 'featured_' . $id . '.' . $extension;
  }

  public function getWebPath($id, $extension)
  {
    return $this->path . $this->generateFileNameFromId($id, $extension);
  }
}
