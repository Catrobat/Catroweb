<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use App\Utils\Utils;
use Imagick;
use ImagickException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

class ScreenshotRepository
{
  /**
   * @var string
   */
  const DEFAULT_SCREENSHOT = 'images/default/screenshot.png';
  /**
   * @var string
   */
  const DEFAULT_THUMBNAIL = 'images/default/thumbnail.png';

  private string $thumbnail_dir;

  private string $thumbnail_path;

  private string $screenshot_dir;

  private string $screenshot_path;

  private ?Imagick $imagick = null;

  private string $tmp_path;

  private string $tmp_dir;

  public function __construct(string $screenshot_dir, string $screenshot_path, string $thumbnail_dir, string $thumbnail_path, string $tmp_dir, string $tmp_path)
  {
    $screenshot_dir = preg_replace('#([^/]+)$#', '$1/', $screenshot_dir);
    $screenshot_path = preg_replace('#([^/]+)$#', '$1/', $screenshot_path);
    $thumbnail_dir = preg_replace('#([^/]+)$#', '$1/', $thumbnail_dir);
    $thumbnail_path = preg_replace('#([^/]+)$#', '$1/', $thumbnail_path);
    $tmp_dir = preg_replace('#([^/]+)$#', '$1/', $tmp_dir);
    $tmp_path = preg_replace('#([^/]+)$#', '$1/', $tmp_path);

    if (!is_dir($screenshot_dir))
    {
      throw new InvalidStorageDirectoryException($screenshot_dir.' is not a valid directory');
    }
    if (!is_dir($thumbnail_dir))
    {
      throw new InvalidStorageDirectoryException($thumbnail_dir.' is not a valid directory');
    }

    if (!is_dir($tmp_dir))
    {
      throw new InvalidStorageDirectoryException($tmp_dir.' is not a valid directory');
    }

    $this->screenshot_dir = $screenshot_dir;
    $this->thumbnail_dir = $thumbnail_dir;
    $this->tmp_dir = $tmp_dir;

    $this->screenshot_path = $screenshot_path;
    $this->thumbnail_path = $thumbnail_path;
    $this->tmp_path = $tmp_path;
  }

  /**
   * @throws ImagickException
   */
  public function saveProgramAssets(string $screenshot_filepath, string $id): void
  {
    $this->saveScreenshot($screenshot_filepath, $id);
    $this->saveThumbnail($screenshot_filepath, $id);
  }

  public function storeImageInTmp(string $image, string $id): void
  {
    $filesystem = new Filesystem();
    $tmp_file_path = $this->tmp_dir.$this->generateFileNameFromId($id);
    if ($filesystem->exists($tmp_file_path))
    {
      $filesystem->remove($tmp_file_path);
    }
    $filesystem->copy($image, $tmp_file_path);
  }

  /**
   * @throws ImagickException
   */
  public function updateProgramAssets(string $image, string $id): void
  {
    $this->storeImageInTmp($image, $id);
    $tmp_file_path = $this->tmp_dir.$this->generateFileNameFromId($id);
    $this->saveScreenshot($tmp_file_path, $id);
    $this->saveThumbnail($tmp_file_path, $id);
  }

  /**
   * @throws ImagickException
   */
  public function saveScreenshot(string $filepath, string $id): void
  {
    $screen = $this->getImagick();
    $screen->readImage($filepath);
    $this->saveImagickScreenshot($screen, $id);
    $screen->destroy();
  }

  public function saveScratchScreenshot(int $Scratch_id, string $id): void
  {
    $screen = $this->getImagick();
    $image = file_get_contents('https://cdn2.scratch.mit.edu/get_image/project/'.$Scratch_id.'_480x360.png');
    $screen->readImageBlob($image);
    $this->saveImagickScreenshot($screen, $id);
    $screen->destroy();
  }

  public function getScreenshotWebPath(string $id): string
  {
    $filename = $this->screenshot_dir.$this->generateFileNameFromId($id);
    if (file_exists($filename))
    {
      return $this->screenshot_path.$this->generateFileNameFromId($id).Utils::getTimestampParameter($filename);
    }

    return self::DEFAULT_SCREENSHOT;
  }

  /**
   * @param string|int $id
   */
  public function getThumbnailWebPath($id): string
  {
    $filename = $this->thumbnail_dir.$this->generateFileNameFromId((string) $id);
    if (file_exists($filename))
    {
      return $this->thumbnail_path.$this->generateFileNameFromId((string) $id).Utils::getTimestampParameter($filename);
    }

    return self::DEFAULT_THUMBNAIL;
  }

  public function importProgramAssets(string $screenshot_filepath, string $thumbnail_filepath, string $id): void
  {
    $filesystem = new Filesystem();
    $filesystem->copy($screenshot_filepath, $this->screenshot_dir.$this->generateFileNameFromId($id));
    $filesystem->copy($thumbnail_filepath, $this->thumbnail_dir.$this->generateFileNameFromId($id));
  }

  /**
   * @throws ImagickException
   */
  public function getImagick(): Imagick
  {
    if (null == $this->imagick)
    {
      $this->imagick = new Imagick();
    }

    return $this->imagick;
  }

  public function deleteThumbnail(string $id): void
  {
    $this->deleteFiles($this->thumbnail_dir, $id);
  }

  public function deleteScreenshot(string $id): void
  {
    $this->deleteFiles($this->screenshot_dir, $id);
  }

  /**
   * @throws ImagickException
   */
  public function saveProgramAssetsTemp(string $screenshot_filepath, string $id): void
  {
    $this->saveScreenshotTemp($screenshot_filepath, $id);
    $this->saveThumbnailTemp($screenshot_filepath, $id);
  }

  public function makeTempProgramAssetsPerm(string $id): void
  {
    $this->makeScreenshotPerm($id);
    $this->makeThumbnailPerm($id);
  }

  public function makeScreenshotPerm(string $id): void
  {
    $filesystem = new Filesystem();
    $filesystem->copy($this->tmp_dir.$this->generateFileNameFromId($id), $this->screenshot_dir.$this->generateFileNameFromId($id));
    $filesystem->remove($this->tmp_dir.$this->generateFileNameFromId($id));
  }

  public function makeThumbnailPerm(string $id): void
  {
    $filesystem = new Filesystem();
    $filesystem->copy($this->tmp_dir.'thumb/'.$this->generateFileNameFromId($id), $this->thumbnail_dir.$this->generateFileNameFromId($id));
    $filesystem->remove($this->tmp_dir.'thumb/'.$this->generateFileNameFromId($id));
  }

  /**
   * @throws ImagickException
   */
  public function saveScreenshotTemp(string $filepath, string $id): void
  {
    $screen = $this->getImagick();
    $screen->readImage($filepath);
    $screen->cropThumbnailImage(480, 480);

    $filename = $this->tmp_dir.$this->generateFileNameFromId($id);
    if (file_exists($filename))
    {
      unlink($filename);
    }
    $screen->writeImage($filename);
    chmod($filename, 0777);
    $screen->destroy();
  }

  public function deleteTempFilesForProgram(string $id): void
  {
    $fs = new Filesystem();
    $fs->remove(
      [
        $this->tmp_dir.$this->generateFileNameFromId($id),
        $this->tmp_dir.'thumb/'.$this->generateFileNameFromId($id),
      ]);
  }

  public function deletePermProgramAssets(string $id): void
  {
    $this->deleteScreenshot($id);
    $this->deleteThumbnail($id);
    $this->deleteTempFilesForProgram($id);
  }

  /**
   * This function empties the tmp folder.
   * When this function is used while a user is
   * uploading a program you will kill the process.
   * So don't use it. It's for testing purposes.
   */
  public function deleteTempFiles(): void
  {
    Utils::removeDirectory($this->tmp_dir);
  }

  /**
   * @throws ImagickException
   */
  private function saveThumbnail(string $filepath, string $id): void
  {
    $thumb = $this->getImagick();
    $thumb->readImage($filepath);
    $thumb->cropThumbnailImage(80, 80);

    $filename = $this->thumbnail_dir.$this->generateFileNameFromId($id);
    if (file_exists($filename))
    {
      unlink($filename);
    }
    $thumb->writeImage($filename);
    chmod($filename, 0777);
    $thumb->destroy();
  }

  private function generateFileNameFromId(string $id): string
  {
    return 'screen_'.$id.'.png';
  }

  private function deleteFiles(string $directory, string $id): void
  {
    try
    {
      $file = new File($directory.$this->generateFileNameFromId($id));
      unlink($file->getPathname());
    }
    catch (FileNotFoundException $fileNotFoundException)
    {
    }
  }

  /**
   * @throws ImagickException
   */
  private function saveThumbnailTemp(string $filepath, string $id): void
  {
    $thumb = $this->getImagick();
    $thumb->readImage($filepath);
    $thumb->cropThumbnailImage(80, 80);

    $filename = $this->tmp_dir.'thumb/'.$this->generateFileNameFromId($id);
    if (file_exists($filename))
    {
      unlink($filename);
    }
    $thumb->writeImage($filename);
    chmod($filename, 0777);
    $thumb->destroy();
  }

  private function saveImagickScreenshot(Imagick $screen, string $id): void
  {
    $screen->cropThumbnailImage(480, 480);

    $filename = $this->screenshot_dir.$this->generateFileNameFromId($id);
    if (file_exists($filename))
    {
      unlink($filename);
    }
    $screen->writeImage($filename);
    chmod($filename, 0777);
  }
}
