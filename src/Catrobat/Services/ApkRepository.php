<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

class ApkRepository
{
  private ?string $dir;

  public function __construct(ParameterBagInterface $parameter_bag)
  {
    $apk_dir = $parameter_bag->get('catrobat.apk.dir');
    $dir = preg_replace('#([^/]+)$#', '$1/', $apk_dir);

    if (!is_dir($dir))
    {
      throw new InvalidStorageDirectoryException($dir.' is not a valid directory');
    }

    $this->dir = $dir;
  }

  public function save(File $file, string $id): void
  {
    $file->move($this->dir, $this->generateFileNameFromId($id));
  }

  public function remove(string $id): void
  {
    $path = $this->dir.$this->generateFileNameFromId($id);
    if (is_file($path))
    {
      unlink($path);
    }
  }

  public function getProgramFile(string $id): File
  {
    return new File($this->dir.$this->generateFileNameFromId($id));
  }

  private function generateFileNameFromId(string $id): string
  {
    return $id.'.apk';
  }
}
