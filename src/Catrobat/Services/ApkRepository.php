<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class ApkRepository
 * @package App\Catrobat\Services
 */
class ApkRepository
{
  /**
   * @var string|string[]|null
   */
  private $dir;

  /**
   * ApkRepository constructor.
   *
   * @param $dir
   */
  public function __construct($dir)
  {
    $dir = preg_replace('/([^\/]+)$/', '$1/', $dir);

    if (!is_dir($dir))
    {
      throw new InvalidStorageDirectoryException($dir . ' is not a valid directory');
    }

    $this->dir = $dir;
  }

  /**
   * @param $file File
   * @param $id
   */
  public function save($file, $id)
  {
    $file->move($this->dir, $this->generateFileNameFromId($id));
  }

  /**
   * @param $id
   */
  public function remove($id)
  {
    $path = $this->dir . $this->generateFileNameFromId($id);
    if (is_file($path))
    {
      unlink($path);
    }
  }

  /**
   * @param $id
   *
   * @return string
   */
  private function generateFileNameFromId($id)
  {
    return $id . '.apk';
  }

  /**
   * @param $id
   *
   * @return File
   */
  public function getProgramFile($id)
  {
    return new File($this->dir . $this->generateFileNameFromId($id));
  }
}
