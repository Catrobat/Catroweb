<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use Symfony\Component\HttpFoundation\File\File;


/**
 * Class FeaturedImageRepository
 * @package App\Catrobat\Services
 */
class FeaturedImageRepository
{
  /**
   * @var string|string[]|null
   */
  private $dir;
  /**
   * @var string|string[]|null
   */
  private $path;

  /**
   * FeaturedImageRepository constructor.
   *
   * @param $dir
   * @param $path
   */
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

  /**
   * @param $file File
   * @param $id
   * @param $extension
   */
  public function save($file, $id, $extension)
  {
    $file->move($this->dir, $this->generateFileNameFromId($id, $extension));
  }

  /**
   * @param $id
   * @param $extension
   */
  public function remove($id, $extension)
  {
    $path = $this->dir . $this->generateFileNameFromId($id, $extension);
    if (is_file($path))
    {
      unlink($path);
    }
  }

  /**
   * @param $id
   * @param $extension
   *
   * @return string
   */
  private function generateFileNameFromId($id, $extension)
  {
    return 'featured_' . $id . '.' . $extension;
  }

  /**
   * @param $id
   * @param $extension
   *
   * @return string
   */
  public function getWebPath($id, $extension)
  {
    return $this->path . $this->generateFileNameFromId($id, $extension);
  }
}
