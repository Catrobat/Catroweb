<?php

namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException;

class ScreenshotRepository
{
  private $thumbnail_dir;
  private $thumbnail_path;
  private $screenshot_dir;
  private $screenshot_path;
  
  public function __construct($screenshot_dir, $screenshot_path, $thumbnail_dir, $thumbnail_path)
  {
    $screenshot_dir = preg_replace('/([^\/]+)$/', '$1/', $screenshot_dir);
    $screenshot_path = preg_replace('/([^\/]+)$/', '$1/', $screenshot_path);
    $thumbnail_dir = preg_replace('/([^\/]+)$/', '$1/', $thumbnail_dir);
    $thumbnail_path = preg_replace('/([^\/]+)$/', '$1/', $thumbnail_path);
    
    if (!is_dir($screenshot_dir))
    {
      throw new InvalidStorageDirectoryException($screenshot_dir . " is not a valid directory");
    }
    if (!is_dir($thumbnail_dir))
    {
      throw new InvalidStorageDirectoryException($thumbnail_dir . " is not a valid directory");
    }
    
    $this->screenshot_dir = $screenshot_dir;
    $this->thumbnail_dir = $thumbnail_dir;
    $this->screenshot_path = $screenshot_path;
    $this->thumbnail_path = $thumbnail_path;
  }
  
  public function saveProgramAssets($screenshot_filepath,$id)
  {
    $this->saveScreenshot($screenshot_filepath, $id);
    $this->saveThumbnail($screenshot_filepath, $id);
  }
  
  public function saveScreenshot($filepath,$id)
  {
    $screen = new \Imagick($filepath);
    $screen->resizeImage(480,480,\Imagick::FILTER_LANCZOS,1);
    $screen->writeImage($this->screenshot_dir . $this->generateFileNameFromId($id));
    $screen->destroy();
  }
  
  private function saveThumbnail($filepath,$id)
  {
    $thumb = new \Imagick($filepath);
    $thumb->resizeImage(80,80,\Imagick::FILTER_LANCZOS,1);
    $thumb->writeImage($this->thumbnail_dir . $this->generateFileNameFromId($id));
    $thumb->destroy();
  }
 
  private function generateFileNameFromId($id)
  {
    return "screen_" . $id . ".png";
  }
  
  public function getScreenshotWebPath($id)
  {
    return $this->screenshot_path . $this->generateFileNameFromId($id);
  }

  public function getThumbnailWebPath($id)
  {
    return $this->thumbnail_path . $this->generateFileNameFromId($id);
  }
  
}
