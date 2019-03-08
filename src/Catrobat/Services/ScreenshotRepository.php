<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Class ScreenshotRepository
 * @package App\Catrobat\Services
 */
class ScreenshotRepository
{
  const DEFAULT_SCREENSHOT = 'images/default/screenshot.png';
  const DEFAULT_THUMBNAIL = 'images/default/thumbnail.png';
  /**
   * @var string|string[]|null
   */
  private $thumbnail_dir;
  /**
   * @var string|string[]|null
   */
  private $thumbnail_path;
  /**
   * @var string|string[]|null
   */
  private $screenshot_dir;
  /**
   * @var string|string[]|null
   */
  private $screenshot_path;
  /**
   * @var
   */
  private $imagick;
  /**
   * @var string|string[]|null
   */
  private $tmp_path;
  /**
   * @var string|string[]|null
   */
  private $tmp_dir;

  /**
   * ScreenshotRepository constructor.
   *
   * @param $screenshot_dir
   * @param $screenshot_path
   * @param $thumbnail_dir
   * @param $thumbnail_path
   * @param $tmp_dir
   * @param $tmp_path
   */
  public function __construct($screenshot_dir, $screenshot_path, $thumbnail_dir, $thumbnail_path, $tmp_dir, $tmp_path)
  {
    $screenshot_dir = preg_replace('/([^\/]+)$/', '$1/', $screenshot_dir);
    $screenshot_path = preg_replace('/([^\/]+)$/', '$1/', $screenshot_path);
    $thumbnail_dir = preg_replace('/([^\/]+)$/', '$1/', $thumbnail_dir);
    $thumbnail_path = preg_replace('/([^\/]+)$/', '$1/', $thumbnail_path);
    $tmp_dir = preg_replace('/([^\/]+)$/', '$1/', $tmp_dir);
    $tmp_path = preg_replace('/([^\/]+)$/', '$1/', $tmp_path);

    if (!is_dir($screenshot_dir))
    {
      throw new InvalidStorageDirectoryException($screenshot_dir . ' is not a valid directory');
    }
    if (!is_dir($thumbnail_dir))
    {
      throw new InvalidStorageDirectoryException($thumbnail_dir . ' is not a valid directory');
    }

    if (!is_dir($tmp_dir))
    {
      throw new InvalidStorageDirectoryException($tmp_dir . ' is not a valid directory');
    }

    $this->screenshot_dir = $screenshot_dir;
    $this->thumbnail_dir = $thumbnail_dir;
    $this->tmp_dir = $tmp_dir;

    $this->screenshot_path = $screenshot_path;
    $this->thumbnail_path = $thumbnail_path;
    $this->tmp_path = $tmp_path;
  }

  /**
   * @param $screenshot_filepath
   * @param $id
   *
   * @throws \ImagickException
   */
  public function saveProgramAssets($screenshot_filepath, $id)
  {
    $this->saveScreenshot($screenshot_filepath, $id);
    $this->saveThumbnail($screenshot_filepath, $id);
  }

  /**
   * @param $filepath
   * @param $id
   *
   * @throws \ImagickException
   */
  public function saveScreenshot($filepath, $id)
  {
    $screen = $this->getImagick();
    $screen->readImage($filepath);
    $screen->resizeImage(480, 480, \Imagick::FILTER_LANCZOS, 1);
    $screen->writeImage($this->screenshot_dir . $this->generateFileNameFromId($id));
    $screen->destroy();
  }

  /**
   * @param $filepath
   * @param $id
   *
   * @throws \ImagickException
   */
  private function saveThumbnail($filepath, $id)
  {
    $thumb = $this->getImagick();
    $thumb->readImage($filepath);
    $thumb->resizeImage(80, 80, \Imagick::FILTER_LANCZOS, 1);
    $thumb->writeImage($this->thumbnail_dir . $this->generateFileNameFromId($id));
    $thumb->destroy();
  }

  /**
   * @param $id
   *
   * @return string
   */
  private function generateFileNameFromId($id)
  {
    return 'screen_' . $id . '.png';
  }

  /**
   * @param $id
   *
   * @return string
   */
  public function getScreenshotWebPath($id)
  {
    if (file_exists($this->screenshot_dir . $this->generateFileNameFromId($id)))
    {
      return $this->screenshot_path . $this->generateFileNameFromId($id);
    }

    return self::DEFAULT_SCREENSHOT;
  }

  /**
   * @param $id
   *
   * @return string
   */
  public function getThumbnailWebPath($id)
  {
    if (file_exists($this->thumbnail_dir . $this->generateFileNameFromId($id)))
    {
      return $this->thumbnail_path . $this->generateFileNameFromId($id);
    }

    return self::DEFAULT_THUMBNAIL;
  }

  /**
   * @param $screenshot_filepath
   * @param $thumbnail_filepath
   * @param $id
   */
  public function importProgramAssets($screenshot_filepath, $thumbnail_filepath, $id)
  {
    $filesystem = new Filesystem();
    $filesystem->copy($screenshot_filepath, $this->screenshot_dir . $this->generateFileNameFromId($id));
    $filesystem->copy($thumbnail_filepath, $this->thumbnail_dir . $this->generateFileNameFromId($id));
  }

  /**
   * @return \Imagick
   * @throws \ImagickException
   */
  public function getImagick()
  {
    if ($this->imagick == null)
    {
      $this->imagick = new \Imagick();
    }

    return $this->imagick;
  }

  /**
   * @param $id
   */
  public function deleteThumbnail($id)
  {
    $this->deleteFiles($this->thumbnail_dir, $id);
  }

  /**
   * @param $id
   */
  public function deleteScreenshot($id)
  {
    $this->deleteFiles($this->screenshot_dir, $id);
  }

  /**
   * @param $directory
   * @param $id
   */
  private function deleteFiles($directory, $id)
  {
    try
    {
      $file = new File($directory . $this->generateFileNameFromId($id));
      unlink($file->getPathname());
    } catch (FileNotFoundException $e)
    {
    }
  }

  /**
   * @param $screenshot_filepath
   * @param $id
   *
   * @throws \ImagickException
   */
  public function saveProgramAssetsTemp($screenshot_filepath, $id)
  {
    $this->saveScreenshotTemp($screenshot_filepath, $id);
    $this->saveThumbnailTemp($screenshot_filepath, $id);
  }

  /**
   * @param $id
   */
  public function makeTempProgramAssetsPerm($id)
  {
    $this->makeScreenshotPerm($id);
    $this->makeThumbnailPerm($id);
  }

  /**
   * @param $id
   */
  public function makeScreenshotPerm($id)
  {
    $filesystem = new Filesystem();
    $filesystem->copy($this->tmp_dir . $this->generateFileNameFromId($id), $this->screenshot_dir . $this->generateFileNameFromId($id));
    $filesystem->remove($this->tmp_dir . $this->generateFileNameFromId($id));
  }

  /**
   * @param $id
   */
  public function makeThumbnailPerm($id)
  {
    $filesystem = new Filesystem();
    $filesystem->copy($this->tmp_dir . "thumb/" . $this->generateFileNameFromId($id), $this->thumbnail_dir . $this->generateFileNameFromId($id));
    $filesystem->remove($this->tmp_dir . "thumb/" . $this->generateFileNameFromId($id));
  }

  /**
   * @param $filepath
   * @param $id
   *
   * @throws \ImagickException
   */
  public function saveScreenshotTemp($filepath, $id)
  {
    $screen = $this->getImagick();
    $screen->readImage($filepath);
    $screen->resizeImage(480, 480, \Imagick::FILTER_LANCZOS, 1);
    $screen->writeImage($this->tmp_dir . $this->generateFileNameFromId($id));
    $screen->destroy();
  }

  /**
   * @param $filepath
   * @param $id
   *
   * @throws \ImagickException
   */
  private function saveThumbnailTemp($filepath, $id)
  {
    $thumb = $this->getImagick();
    $thumb->readImage($filepath);
    $thumb->resizeImage(80, 80, \Imagick::FILTER_LANCZOS, 1);
    $thumb->writeImage($this->tmp_dir . "thumb/" . $this->generateFileNameFromId($id));
    $thumb->destroy();
  }

  /**
   * @param $id
   *
   * @desc
   */
  public function deleteTempFilesForProgram($id)
  {
    $fs = new Filesystem();
    $fs->remove(
      [
        $this->tmp_dir . $this->generateFileNameFromId($id),
        $this->tmp_dir . "thumb/" . $this->generateFileNameFromId($id),
        $this->tmp_dir . $id . ".catrobat",
      ]);
  }

  /**
   * @param $id
   */
  public function deletePermProgramAssets($id)
  {
    $this->deleteScreenshot($id);
    $this->deleteThumbnail($id);
    $this->deleteTempFilesForProgram($id);
  }

  /**
   * @desc This function empties the tmp folder.
   *       When this function is used while a user is
   *       uploading a program you will kill the process.
   *       So don't use it. It's for testing purposes.
   *
   */
  public function deleteTempFiles()
  {
    $this->removeDirectory($this->tmp_dir);
  }

  /**
   * @param $directory
   */
  private function removeDirectory($directory)
  {
    foreach (glob("{$directory}*") as $file)
    {
      if (is_dir($file))
      {
        $this->recursiveRemoveDirectory($file);
      }
      else
      {
        unlink($file);
      }
    }
  }

  /**
   * @param $directory
   */
  private function recursiveRemoveDirectory($directory)
  {
    foreach (glob("{$directory}/*") as $file)
    {
      if (is_dir($file))
      {
        $this->recursiveRemoveDirectory($file);
      }
      else
      {
        unlink($file);
      }
    }
    rmdir($directory);
  }

}
