<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\Storage\FileHelper;
use App\Utils\TimeUtils;
use Symfony\Component\HttpFoundation\File\File;

class CatrobatFileExtractor
{
  private readonly string $extract_dir;

  public function __construct(
    string $extract_dir,
    private readonly string $extract_path,
  ) {
    FileHelper::verifyDirectoryExists($extract_dir);
    $this->extract_dir = $extract_dir;
  }

  /**
   * @throws \Exception
   */
  public function extract(File $file): ExtractedCatrobatFile
  {
    $temp_path = hash('md5', TimeUtils::getTimestamp().random_int(0, mt_getrandmax()));
    $full_extract_dir = $this->extract_dir.$temp_path.'/';
    $full_extract_path = $this->extract_path.$temp_path.'/';

    $zip = new \ZipArchive();
    $res = $zip->open($file->getPathname());

    if (true === $res) {
      $zip->extractTo($full_extract_dir);
      $zip->close();
    } else {
      throw new InvalidCatrobatFileException('errors.file.invalid', 505);
    }

    return new ExtractedCatrobatFile($full_extract_dir, $full_extract_path, $temp_path);
  }

  public function getExtractDir(): string
  {
    return $this->extract_dir;
  }

  public function getExtractPath(): string
  {
    return $this->extract_path;
  }
}
