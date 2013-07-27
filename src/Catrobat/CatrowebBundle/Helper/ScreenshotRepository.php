<?php

namespace Catrobat\CatrowebBundle\Helper;

use Catrobat\CatrowebBundle\Exceptions\InvalidStorageDirectoryException;

class ScreenshotRepository
{
  private $webdir;
  private $thumbnail_dir;
  private $screenshot_dir;
  
  public function __construct($webdir, $screenshot_dir, $thumbnail_dir)
  {
    $screenshot_dir = preg_replace('/([^\/]+)$/', '$1/', $screenshot_dir);
    $thumbnail_dir = preg_replace('/([^\/]+)$/', '$1/', $thumbnail_dir);
    $webdir = preg_replace('/([^\/]+)$/', '$1/', $webdir);
    
    if (!is_dir($webdir.$screenshot_dir))
    {
      throw new InvalidStorageDirectoryException();
    }
    if (!is_dir($webdir.$thumbnail_dir))
    {
      throw new InvalidStorageDirectoryException();
    }
    
    $this->webdir = $webdir;
    $this->screenshot_dir = $screenshot_dir;
    $this->thumbnail_dir = $thumbnail_dir;
  }
  
  public function saveProjectAssets($screenshot_filepath,$id)
  {
    $this->saveScreenshot($screenshot_filepath, $id);
    $this->saveThumbnail($screenshot_filepath, $id);
  }
  
  public function saveScreenshot($filepath,$id)
  {
    $screen = new \Imagick($filepath);
    $screen->resizeImage(480,480,\Imagick::FILTER_LANCZOS,1);
    $screen->writeImage($this->webdir . $this->screenshot_dir . $this->generateFileNameFromId($id));
    $screen->destroy();
  }
  
  private function saveThumbnail($filepath,$id)
  {
    $thumb = new \Imagick($filepath);
    $thumb->resizeImage(80,80,\Imagick::FILTER_LANCZOS,1);
    $thumb->writeImage($this->webdir . $this->thumbnail_dir . $this->generateFileNameFromId($id));
    $thumb->destroy();
  }
 
  private function generateFileNameFromId($id)
  {
    return "screen_" . $id . ".png";
  }
  
  public function getScreenshotWebPath($id)
  {
    return $this->screenshot_dir . $this->generateFileNameFromId($id);
  }

  public function getThumbnailWebPath($id)
  {
    return $this->thumbnail_dir . $this->generateFileNameFromId($id);
  }
  
}
