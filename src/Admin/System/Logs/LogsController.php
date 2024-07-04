<?php

declare(strict_types=1);

namespace App\Admin\System\Logs;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-extends CRUDController<LogLine>
 */
class LogsController extends CRUDController
{
  final public const string LOG_DIR = '../var/log/';

  final public const string LOG_PATTERN = '*.log';

  final public const int FILTER_LEVEL_DEBUG = 0;

  final public const int FILTER_LEVEL_INFO = 1;

  final public const int FILTER_LEVEL_NOTICE = 2;

  final public const int FILTER_LEVEL_WARNING = 3;

  final public const int FILTER_LEVEL_ERROR = 4;

  final public const int FILTER_LEVEL_CRITICAL = 5;

  final public const int FILTER_LEVEL_ALERT = 6;

  final public const int FILTER_LEVEL_EMERGENCY = 7;

  #[\Override]
  public function listAction(Request $request): Response
  {
    $filter = self::FILTER_LEVEL_WARNING;
    $greater_equal_than_level = true;
    $line_count = 20;
    $file = null;
    if ($request->isXmlHttpRequest()) {
      if ($request->query->get('count')) {
        $line_count = $request->query->getInt('count');
      }

      if (false !== $request->query->get('filter')) {
        $filter = $request->query->getInt('filter');
      }

      if ($request->query->get('greaterThan')) {
        $greater_equal_than_level = $request->query->getBoolean('greaterThan');
      }

      if ($request->query->get('file')) {
        $file = (string) $request->query->get('file');
      }
    }

    $searchParam = [];
    $searchParam['filter'] = $filter;
    $searchParam['greater_equal_than_level'] = $greater_equal_than_level;
    $searchParam['line_count'] = $line_count;
    $allFiles = $this->getAllFilesInDirByPattern(self::LOG_DIR, self::LOG_PATTERN);
    if (empty($file)) {
      $file = $allFiles[0];
    }

    return $this->renderWithExtraParams('Admin/Tools/logs.html.twig', [
      'files' => $allFiles, 'content' => $this->getLogFileContent($file, self::LOG_DIR, $searchParam), ]
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
    $file = popen('tac '.$filePath, 'r');

    $index = 0;
    $content = [];
    while (($line = fgets($file)) && ($index < $searchParam['line_count'])) {
      $log_line = new LogLine($line);

      if (($searchParam['greater_equal_than_level'] && $log_line->getDebugLevel() >= $searchParam['filter'])
        || (!$searchParam['greater_equal_than_level'] && $log_line->getDebugLevel() == $searchParam['filter'])
      ) {
        $content[$index] = $log_line;

        ++$index;
      }
    }

    pclose($file);

    return $content;
  }
}
