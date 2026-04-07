<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\Storage\FileHelper;
use App\Utils\TimeUtils;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;

class CatrobatFileExtractor
{
  private readonly string $extract_dir;

  /**
   * @throws \Exception
   */
  public function __construct(
    #[Autowire('%catrobat.file.extract.dir%')]
    string $extract_dir,
    #[Autowire('%catrobat.file.extract.path%')]
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

    if (true !== $res) {
      throw new InvalidCatrobatFileException('errors.file.invalid', 505);
    }

    $max_total_size = 200 * 1024 * 1024; // 200 MB
    $max_file_count = 5_000;
    $total_size = 0;
    $num_entries = $zip->numFiles;

    if ($num_entries > $max_file_count) {
      $zip->close();
      throw new InvalidCatrobatFileException('errors.file.invalid', 505, 'Too many files in archive');
    }

    for ($i = 0; $i < $num_entries; ++$i) {
      $stat = $zip->statIndex($i);
      if (false === $stat) {
        $zip->close();
        throw new InvalidCatrobatFileException('errors.file.invalid', 505, 'Cannot read archive entry');
      }

      $entry_name = $stat['name'];
      if (str_contains($entry_name, '..') || str_starts_with($entry_name, '/')) {
        $zip->close();
        throw new InvalidCatrobatFileException('errors.file.invalid', 505, 'Path traversal detected');
      }

      $total_size += $stat['size'];
      if ($total_size > $max_total_size) {
        $zip->close();
        throw new InvalidCatrobatFileException('errors.file.invalid', 505, 'Uncompressed size exceeds limit');
      }
    }

    $zip->extractTo($full_extract_dir);
    $zip->close();

    // Some Catrobat apps wrap project files in a subdirectory. If code.xml is not at the
    // root but exists inside a single subdirectory, promote that subdirectory to root.
    if (!file_exists($full_extract_dir.'code.xml')) {
      $entries = array_diff((array) scandir($full_extract_dir), ['.', '..']);
      if (1 === count($entries)) {
        $single_entry = reset($entries);
        $nested_dir = $full_extract_dir.$single_entry.'/';
        if (is_dir($nested_dir) && file_exists($nested_dir.'code.xml')) {
          $this->promoteDirectoryContents($nested_dir, $full_extract_dir);
        }
      }
    }

    return new ExtractedCatrobatFile($full_extract_dir, $full_extract_path, $temp_path);
  }

  private function promoteDirectoryContents(string $nested_dir, string $target_dir): void
  {
    $items = array_diff((array) scandir($nested_dir), ['.', '..']);
    foreach ($items as $item) {
      rename($nested_dir.$item, $target_dir.$item);
    }
    rmdir($nested_dir);
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
