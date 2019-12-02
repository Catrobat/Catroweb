<?php

namespace App\Catrobat\Services;

use App\Catrobat\Services\CatrobatCodeParser\CatrobatCodeParser;
use App\Catrobat\Services\CatrobatCodeParser\ParsedScene;
use App\Catrobat\Services\CatrobatCodeParser\ParsedSceneProgram;
use App\Catrobat\Services\CatrobatCodeParser\ParsedSimpleProgram;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class CatrobatFileSanitizer
 * @package App\Catrobat\Services
 *
 * All files not mentioned in the code.xml should be removed.
 * Only exceptions: 1x code.xml, 1x permissions.txt, 1x (automatic|manual)_screenhot.png
 */
class CatrobatFileSanitizer
{
  /**
   * @var $scenes
   */
  private $scenes;

  /**
   * @var $image_paths array
   */
  private $image_paths;

  /**
   * @var $sound_paths array
   */
  private $sound_paths;

  /**
   * @var $screenshot_path string
   */
  private $screenshot_path;

  /**
   * @var $extracted_file_root_path string
   */
  private $extracted_file_root_path;

  /**
   * @var $scenes array
   */
  private $catrobat_code_parser;

  /**
   * CatrobatFileSanitizer constructor.
   *
   * @param CatrobatCodeParser $catrobat_code_parser
   */
  public function __construct(CatrobatCodeParser $catrobat_code_parser)
  {
    $this->catrobat_code_parser = $catrobat_code_parser;
  }

  /**
   * @param ExtractedCatrobatFile $extracted_file
   *
   * @throws \Exception
   */
  public function sanitize(ExtractedCatrobatFile $extracted_file)
  {
    $this->extracted_file_root_path = $extracted_file->getPath();
    $this->sound_paths = $extracted_file->getContainingSoundPaths();
    $this->image_paths = $extracted_file->getContainingImagePaths();
    $this->screenshot_path = $extracted_file->getScreenshotPath();
    $this->scenes = $this->getScenes($extracted_file);

    $files = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($this->extracted_file_root_path, RecursiveDirectoryIterator::SKIP_DOTS),
      RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $file)
    {
      /** @var $file File */
      $filename = $file->getFilename();
      $filepath = $file->getRealPath();
      $relative_filepath = $this->getRelativePath($filepath);

      if ($this->isTheOnlyCodeXmlFile($relative_filepath)
        || $this->isTheOnlyPermissionsFile($relative_filepath)
        || $this->isAValidImageFile($filename, $relative_filepath, $extracted_file)
        || $this->isAValidSoundFile($filename, $relative_filepath, $extracted_file)
        || $this->isFileTheUsedScreenshot($relative_filepath)
        || $this->isAValidSceneDirectory($relative_filepath))
      {
        continue;
      }
      else
      {
        is_file($filepath) ? unlink($filepath) : $this->deleteDirectory($filepath);
      }
    }
  }

  /**
   * @param $relative_filepath
   *
   * @return bool
   */
  private function isTheOnlyCodeXmlFile($relative_filepath)
  {
    // code.xml must only be found once in the root directory
    return $relative_filepath === "/code.xml";
  }

  /**
   * @param $relative_filepath
   *
   * @return bool
   */
  private function isTheOnlyPermissionsFile($relative_filepath)
  {
    // permissions.txt must only be found once in the root directory
    return $relative_filepath === "/permissions.txt";
  }

  /**
   * @param $relative_filepath
   *
   * @return bool
   */
  private function isFileTheUsedScreenshot($relative_filepath)
  {
    // the app uploads multiple screenshots, but we only need one
    return $this->getRelativePath($this->screenshot_path) === $relative_filepath;
  }

  /**
   * @param $relative_filepath
   *
   * @return bool
   */
  private function isAValidSceneDirectory($relative_filepath)
  {
    // Besides image and sound directories the root directory can contain a directory for every scene.
    foreach ($this->scenes as $scene)
    {
      if ($relative_filepath === "/" . $scene)
      {
        return true;
      }
    }

    return false;
  }

  /**
   * @param $filename
   * @param $relative_filepath
   * @param ExtractedCatrobatFile $extracted_file
   *
   * @return bool
   * @throws \Exception
   */
  private function isAValidSoundFile($filename, $relative_filepath, ExtractedCatrobatFile $extracted_file)
  {
    return $this->isAValidImageOrSoundFile("/sounds", $this->sound_paths, $filename, $relative_filepath, $extracted_file);
  }

  /**
   * @param $filename
   * @param $relative_filepath
   * @param ExtractedCatrobatFile $extracted_file
   *
   * @return bool
   */
  private function isAValidImageFile($filename, $relative_filepath, ExtractedCatrobatFile $extracted_file)
  {
    return $this->isAValidImageOrSoundFile("/images", $this->image_paths, $filename, $relative_filepath, $extracted_file);
  }

  /**
   * @param $dir_name
   * @param $paths_array
   * @param $filename
   * @param $relative_filepath
   * @param ExtractedCatrobatFile $extracted_file
   *
   * @return bool
   */
  private function isAValidImageOrSoundFile($dir_name, $paths_array, $filename, $relative_filepath,
                                            ExtractedCatrobatFile $extracted_file)
  {

    // Here we must accept:
    //   - image and sound directories in the root directory.
    //   - image and sound directories in Scene directories
    //   - image and sound files when they are mentioned in the code.xml

    if ($relative_filepath === $dir_name)
    {
      return true;
    }

    foreach ($this->scenes as $scene)
    {
      if ($relative_filepath === "/" . $scene . $dir_name)
      {
        return true;
      }
    }

    foreach ($paths_array as $path)
    {
      if ($extracted_file->isFileMentionedInXml($filename) && $this->getRelativePath($path) === $relative_filepath)
      {
        return true;
      }
    }
  }

  /**
   * @param ExtractedCatrobatFile $extracted_file
   *
   * @return array
   */
  private function getScenes(ExtractedCatrobatFile $extracted_file)
  {
    $scenes = [];
    $parsed_project = $this->catrobat_code_parser->parse($extracted_file);
    /** @var $parsed_project ParsedSceneProgram|ParsedSimpleProgram */
    if ($parsed_project !== null && $parsed_project->hasScenes())
    {
      $scenes_array = $parsed_project->getScenes();
      foreach ($scenes_array as $scene)
      {
        /** @var $scene ParsedScene */
        array_push($scenes, $scene->getName());
      }
    }

    return $scenes;
  }

  /**
   * @param $filepath
   *
   * @return mixed
   */
  private function getRelativePath($filepath)
  {
    $needle = @end(preg_split('@/@', $this->extracted_file_root_path, null, PREG_SPLIT_NO_EMPTY));
    $relative_filepath = strstr($filepath, $needle);

    return str_replace($needle, '', $relative_filepath);
  }

  /**
   * @param $dir
   *
   * @return bool
   */
  private function deleteDirectory($dir)
  {
    if (!file_exists($dir))
    {
      return true;
    }

    if (!is_dir($dir))
    {
      return unlink($dir);
    }

    foreach (scandir($dir) as $item)
    {
      if ($item == '.' || $item == '..')
      {
        continue;
      }
      if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item))
      {
        return false;
      }
    }

    return rmdir($dir);
  }
}
