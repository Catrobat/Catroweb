<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\Project\CatrobatCode\Parser\CatrobatCodeParser;
use App\Project\CatrobatCode\Parser\ParsedScene;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\HttpFoundation\File\File;

class CatrobatFileSanitizer
{
  private ?array $scenes = null;

  private ?array $image_paths = null;

  private ?array $sound_paths = null;

  private ?string $extracted_file_root_path = null;

  public function __construct(private readonly CatrobatCodeParser $catrobat_code_parser)
  {
  }

  public function sanitize(ExtractedCatrobatFile $extracted_file): void
  {
    $this->extracted_file_root_path = $extracted_file->getPath();
    $this->sound_paths = $extracted_file->getContainingSoundPaths();
    $this->image_paths = $extracted_file->getContainingImagePaths();
    $this->scenes = $this->getScenes($extracted_file);

    $files = new \RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($this->extracted_file_root_path, RecursiveDirectoryIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $file) {
      /** @var File $file */
      $filename = $file->getFilename();
      $filepath = $file->getRealPath();
      $relative_filepath = $this->getRelativePath($filepath);
      if ($this->isTheOnlyCodeXmlFile($relative_filepath)) {
        continue;
      }
      if ($this->isTheOnlyPermissionsFile($relative_filepath)) {
        continue;
      }
      if ($this->isAValidImageFile($filename, $relative_filepath, $extracted_file)) {
        continue;
      }
      if ($this->isAValidSoundFile($filename, $relative_filepath, $extracted_file)) {
        continue;
      }
      if ($this->isAValidScreenshot($relative_filepath)) {
        continue;
      }
      if ($this->isAValidSceneDirectory($relative_filepath)) {
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

  private function isAValidScreenshot(string $relative_filepath): bool
  {
    // the app uploads multiple screenshots.
    // We only need one, however, we must leave them untouched for the apps to use
    return str_contains($relative_filepath, 'screenshot.png')
      || str_contains($relative_filepath, 'manual_screenshot.png')
      || str_contains($relative_filepath, 'automatic_screenshot.png');
  }

  private function isAValidSceneDirectory(string $relative_filepath): bool
  {
    // Besides image and sound directories the root directory can contain a directory for every scene.
    foreach ($this->scenes as $scene) {
      if ($relative_filepath === '/'.$scene) {
        return true;
      }
    }

    return false;
  }

  private function isAValidSoundFile(string $filename, string $relative_filepath, ExtractedCatrobatFile $extracted_file): bool
  {
    return $this->isAValidFile('/sounds', $this->sound_paths, $filename, $relative_filepath, $extracted_file);
  }

  private function isAValidImageFile(string $filename, string $relative_filepath, ExtractedCatrobatFile $extracted_file): bool
  {
    return $this->isAValidFile('/images', $this->image_paths, $filename, $relative_filepath, $extracted_file);
  }

  private function isAValidFile(string $dir_name, array $paths_array, string $filename,
    string $relative_filepath, ExtractedCatrobatFile $extracted_file): bool
  {
    // Here we must accept:
    //   - image and sound directories in the root directory.
    //   - image and sound directories in Scene directories
    //   - image and sound files when they are mentioned in the code.xml

    if ($relative_filepath === $dir_name) {
      return true;
    }

    foreach ($this->scenes as $scene) {
      if ($relative_filepath === '/'.$scene.$dir_name) {
        return true;
      }
    }

    foreach ($paths_array as $path) {
      if (!$extracted_file->isFileMentionedInXml($filename)) {
        continue;
      }
      if ($this->getRelativePath($path) !== $relative_filepath) {
        continue;
      }

      return true;
    }

    return false;
  }

  private function getScenes(ExtractedCatrobatFile $extracted_file): array
  {
    $scenes = [];
    $parsed_project = $this->catrobat_code_parser->parse($extracted_file);
    if (null !== $parsed_project && $parsed_project->hasScenes()) {
      $scenes_array = $parsed_project->getScenes();
      foreach ($scenes_array as $scene) {
        /* @var $scene ParsedScene */
        $scenes[] = $scene->getName();
      }
    }

    return $scenes;
  }

  private function getRelativePath(?string $filepath): string
  {
    if (null === $filepath) {
      return '';
    }

    $limit = -1;
    $pattern = '@/@';
    $array = preg_split($pattern, (string) $this->extracted_file_root_path, $limit, PREG_SPLIT_NO_EMPTY);
    $needle = @end($array);
    $relative_filepath = strstr($filepath, (string) $needle);

    return str_replace($needle, '', (string) $relative_filepath);
  }

  private function deleteDirectory(string $dir): bool
  {
    if (!file_exists($dir)) {
      return true;
    }

    if (!is_dir($dir)) {
      return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
      if ('.' === $item) {
        continue;
      }
      if ('..' === $item) {
        continue;
      }

      if (!$this->deleteDirectory($dir.DIRECTORY_SEPARATOR.$item)) {
        return false;
      }
    }

    return rmdir($dir);
  }
}
