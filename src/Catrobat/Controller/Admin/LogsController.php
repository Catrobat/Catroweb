<?php

namespace App\Catrobat\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LogsController extends CRUDController
{
  const LOG_DIR = '../var/log/';

  const LOG_PATTERN = '*.log';

  const FILTER_LEVEL_DEBUG = 0;
  const FILTER_LEVEL_INFO = 1;
  const FILTER_LEVEL_NOTICE = 2;

  const FILTER_LEVEL_WARNING = 3;
  const FILTER_LEVEL_ERROR = 4;
  const FILTER_LEVEL_CRITICAL = 5;
  const FILTER_LEVEL_ALERT = 6;
  const FILTER_LEVEL_EMERGENCY = 7;

  public function listAction(Request $request = null): Response
  {
    $filter = self::FILTER_LEVEL_WARNING;
    $greater_equal_than_level = true;
    $line_count = 20;
    if ($request->isXmlHttpRequest())
    {
      if ($request->query->get('count'))
      {
        $line_count = $request->query->getInt('count');
      }
      if (false !== $request->query->get('filter'))
      {
        $filter = $request->query->getInt('filter');
      }
      if ($request->query->get('greaterThan'))
      {
        $greater_equal_than_level = $request->query->getBoolean('greaterThan');
      }
    }
    $searchParam = [];
    $searchParam['filter'] = $filter;
    $searchParam['greater_equal_than_level'] = $greater_equal_than_level;
    $searchParam['line_count'] = $line_count;

    $result = $this->getFilesAndContentByDirAndPattern($searchParam, self::LOG_DIR, self::LOG_PATTERN);

    return $this->renderWithExtraParams('Admin/logs.html.twig', [
      'files' => $result['files'],
      'content' => $result['content'],
    ]);
  }

  protected function getFilesAndContentByDirAndPattern(array $searchParam, string $dir, string $pattern): array
  {
    $finder = new Finder();
    $finder->files()->in($dir)->depth('< 2')->name($pattern);
    $finder->sortByName();

    $files = [];
    foreach ($finder as $file)
    {
      array_push($files, $file->getRelativePathname());
    }

    $content = [];
    for ($i = 0; $i < count($files); ++$i)
    {
      $filename = $dir.$files[$i];
      $file = popen("tac {$filename}", 'r');

      $index = 0;
      while (($line = fgets($file)) && ($index < $searchParam['line_count']))
      {
        $log_line = new LogLine($line);

        if (($searchParam['greater_equal_than_level'] && $log_line->getDebugLevel() >= $searchParam['filter']) ||
          (!$searchParam['greater_equal_than_level'] && $log_line->getDebugLevel() == $searchParam['filter'])
        ) {
          $content[$i][$index] = $log_line;

          ++$index;
        }
      }
      if (!array_key_exists($i, $content))
      {
        $content[$i][0] = new LogLine();
      }
      pclose($file);
    }

    return ['files' => $files, 'content' => $content];
  }
}
