<?php

namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException;
use FOS\RestBundle\Controller\Annotations\Unlink;
use Symfony\Component\HttpFoundation\File\File;

class ApkRepository
{
  private $dir;

  public function __construct($dir)
  {
    $dir = preg_replace('/([^\/]+)$/', '$1/', $dir);

    if (!is_dir($dir))
    {
      throw new InvalidStorageDirectoryException($dir . ' is not a valid directory');
    }

    $this->dir = $dir;
  }

  public function save($file, $id)
  {
    $file->move($this->dir, $this->generateFileNameFromId($id));
  }

  public function remove($id)
  {
    $path = $this->dir . $this->generateFileNameFromId($id);
    if (is_file($path))
    {
      unlink($path);
    }
  }

  private function generateFileNameFromId($id)
  {
    return $id . '.apk';
  }

  public function getProgramFile($id)
  {
    return new File($this->dir . $this->generateFileNameFromId($id));
  }
}
