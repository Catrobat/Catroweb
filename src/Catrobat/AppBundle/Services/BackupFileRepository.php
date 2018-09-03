<?php

namespace Catrobat\AppBundle\Services;


use Symfony\Component\HttpFoundation\File\File;

class BackupFileRepository
{
  private $directory;

  public function __construct($directory)
  {
    if (!is_dir($directory))
    {
      throw new InvalidStorageDirectoryException($directory . ' is not a valid directory');
    }
    $this->directory = $directory;
  }

  public function getDirectory()
  {
    return $this->directory;
  }

  public function getBackupFile($id)
  {
    return new File($this->directory . $id);
  }
}