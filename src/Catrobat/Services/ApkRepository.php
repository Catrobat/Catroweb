<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
   * @param ParameterBagInterface $parameter_bag
   */
  public function __construct(ParameterBagInterface $parameter_bag)
  {
    $apk_dir = $parameter_bag->get('catrobat.apk.dir');
    $dir = preg_replace('/([^\/]+)$/', '$1/', $apk_dir);

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
