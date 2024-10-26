<?php

declare(strict_types=1);

namespace App\Admin\System\Logs;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-extends CRUDController<\stdClass>
 */
class LogsController extends CRUDController
{
  final public const string LOG_DIR = '../var/log/';

  final public const string LOG_PATTERN = '*.log';

  #[\Override]
  public function listAction(Request $request): Response
  {
    $line_count = 5000;
    $file = null;
    if ($request->query->get('count')) {
      $line_count = $request->query->getInt('count');
    }

    if ($request->query->get('file')) {
      $file = (string) $request->query->get('file');
    }

    $searchParam = [];
    $searchParam['line_count'] = $line_count;
    $allFiles = $this->getAllFilesInDirByPattern(self::LOG_DIR, self::LOG_PATTERN);
    if (empty($file) && !empty($allFiles)) {
      $file = $allFiles[0];
    }
    $content = empty($file) ? null : $this->getLogFileContent($file, self::LOG_DIR, $searchParam);

    return $this->render('Admin/SystemManagement/Logs.html.twig', [
      'files' => $allFiles, 'content' => $content, ]
    );
  }

  protected function getAllFilesInDirByPattern(string $dir, string $pattern): array
  {
    $finder = new Finder();
    $finder->files()->in($dir)->depth('>= 1')->name($pattern);
    $finder->sortByName();

    $files = [];
    foreach ($finder as $file) {
      $files[] = $file->getRelativePathname();
    }

    return $files;
  }

  protected function getLogFileContent(string $fileName, string $dir, array $searchParam): array
  {
    $filePath = $dir.$fileName;
    $file = fopen($filePath, 'r');

    $index = 0;
    $content = [];
    $currentLogEntry = null;

    while (($line = fgets($file)) !== false && ($index < $searchParam['line_count'])) {
      $trimmedLine = trim($line);

      if ('' === $trimmedLine) {
        // Start a new log entry if we encounter an empty line
        if (null !== $currentLogEntry) {
          $content[$index] = $currentLogEntry;
          $currentLogEntry = null;
          ++$index;
        }
        continue;
      }

      if (null === $currentLogEntry) {
        // Start a new log entry with the current line as the title
        $currentLogEntry = [
          'title' => $trimmedLine,
          'message' => '',
        ];
      } else {
        // Append to the current log entry's message
        $currentLogEntry['message'] .= $trimmedLine."\n";
      }
    }

    // Add the last log entry if it exists and there is room
    if (null !== $currentLogEntry && $index < $searchParam['line_count']) {
      $content[$index] = $currentLogEntry;
    }

    fclose($file);

    return $content;
  }
}
