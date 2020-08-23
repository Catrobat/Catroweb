<?php

namespace App\Catrobat\Services;

use App\Catrobat\CatrobatCode\Parser\CatrobatCodeParser;
use App\Catrobat\CatrobatCode\Parser\ParsedScene;
use App\Catrobat\CatrobatCode\Parser\ParsedSceneProgram;
use App\Catrobat\CatrobatCode\Parser\ParsedSimpleProgram;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\HttpFoundation\File\File;

class CatrobatFileSanitizer
{
  private ?array $scenes;

  private ?array $image_paths;

  private ?array $sound_paths;

  private ?string $screenshot_path;

  private ?string $extracted_file_root_path;

  private CatrobatCodeParser $catrobat_code_parser;

  public function __construct(CatrobatCodeParser $catrobat_code_parser)
  {
    $this->catrobat_code_parser = $catrobat_code_parser;
  }

  public function sanitize(ExtractedCatrobatFile $extracted_file): void
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
      /** @var File $file */
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

      is_file($filepath) ? unlink($filepath) : $this->deleteDirectory($filepath);
    }
  }

  private function isTheOnlyCodeXmlFile(string $relative_filepath): bool
  {
    // code.xml must only be found once in the root directory
    return '/code.xml' === $relative_filepath;
  }

  private function isTheOnlyPermissionsFile(string $relative_filepath): bool
  {
    // permissions.txt must only be found once in the root directory
    return '/permissions.txt' === $relative_filepath;
  }

  private function isFileTheUsedScreenshot(string $relative_filepath): bool
  {
    // the app uploads multiple screenshots, but we only need one
    return $this->getRelativePath($this->screenshot_path) === $relative_filepath;
  }

  private function isAValidSceneDirectory(string $relative_filepath): bool
  {
    // Besides image and sound directories the root directory can contain a directory for every scene.
    foreach ($this->scenes as $scene)
    {
      if ($relative_filepath === '/'.$scene)
      {
        return true;
      }
    }

    return false;
  }

  private function isAValidSoundFile(string $filename, string $relative_filepath, ExtractedCatrobatFile $extracted_file): bool
  {
    return $this->isAValidImageOrSoundFile('/sounds', $this->sound_paths, $filename, $relative_filepath, $extracted_file);
  }

  private function isAValidImageFile(string $filename, string $relative_filepath, ExtractedCatrobatFile $extracted_file): bool
  {
    return $this->isAValidImageOrSoundFile('/images', $this->image_paths, $filename, $relative_filepath, $extracted_file);
  }

  private function isAValidImageOrSoundFile(string $dir_name, array $paths_array, string $filename, string $relative_filepath,
                                            ExtractedCatrobatFile $extracted_file): bool
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
      if ($relative_filepath === '/'.$scene.$dir_name)
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

    return false;
  }

  private function getScenes(ExtractedCatrobatFile $extracted_file): array
  {
    $scenes = [];
    $parsed_project = $this->catrobat_code_parser->parse($extracted_file);
    /** @var ParsedSceneProgram|ParsedSimpleProgram $parsed_project */
    if (null !== $parsed_project && $parsed_project->hasScenes())
    {
      $scenes_array = $parsed_project->getScenes();
      foreach ($scenes_array as $scene)
      {
        /* @var $scene ParsedScene */
        $scenes[] = $scene->getName();
      }
    }

    return $scenes;
  }

  private function getRelativePath(?string $filepath): string
  {
    if (null === $filepath)
    {
      return '';
    }

    $limit = null;
    $pattern = '@/@';
    $array = preg_split($pattern, $this->extracted_file_root_path, $limit, PREG_SPLIT_NO_EMPTY);
    $needle = @end($array);
    $relative_filepath = strstr($filepath, (string) $needle);

    return str_replace($needle, '', $relative_filepath);
  }

  private function deleteDirectory(string $dir): bool
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
      if ('.' == $item || '..' == $item)
      {
        continue;
      }
      if (!$this->deleteDirectory($dir.DIRECTORY_SEPARATOR.$item))
      {
        return false;
      }
    }

    return rmdir($dir);
  }
}
