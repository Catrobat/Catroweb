<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

class BackupFileRepository
{
  private string $directory;

  public function __construct(ParameterBagInterface $parameter_bag)
  {
    $directory = $parameter_bag->get('catrobat.backup.dir');
    if (!is_dir($directory))
    {
      throw new InvalidStorageDirectoryException($directory.' is not a valid directory');
    }
    $this->directory = $directory;
  }

  public function getDirectory(): string
  {
    return $this->directory;
  }

  public function getBackupFile(string $id): File
  {
    return new File($this->directory.$id);
  }
}
