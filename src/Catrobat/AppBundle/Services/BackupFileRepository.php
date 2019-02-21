<?php

namespace Catrobat\AppBundle\Services;


use Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class BackupFileRepository
 * @package Catrobat\AppBundle\Services
 */
class BackupFileRepository
{
  /**
   * @var
   */
  private $directory;

  /**
   * BackupFileRepository constructor.
   *
   * @param $directory
   */
  public function __construct($directory)
  {
    if (!is_dir($directory))
    {
      throw new InvalidStorageDirectoryException($directory . ' is not a valid directory');
    }
    $this->directory = $directory;
  }

  /**
   * @return mixed
   */
  public function getDirectory()
  {
    return $this->directory;
  }

  /**
   * @param $id
   *
   * @return File
   */
  public function getBackupFile($id)
  {
    return new File($this->directory . $id);
  }
}