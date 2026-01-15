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
    if (!file_exists($filePath)) {
      return [];
    }

    $file = fopen($filePath, 'r');
    $content = [];
    $currentLogEntry = null;
    $index = 0;

    // Regex patterns for different log formats
    // Format 1: [2024-01-15 10:30:45] ERROR app: Message | ip=X user=Y
    $standardPattern = '/^\[([^\]]+)\]\s+(\w+)\s+(\w+):\s+(.+?)(?:\s*\|\s*(.*))?$/';

    // Format 2: 2026-01-15T17:04:46.867976+00:00] php.INFO: Message {json} {json}
    // (may be missing opening bracket)
    $isoPattern = '/^(\[)?(\d{4}-\d{2}-\d{2}T[^\]]+)\]\s+(\w+)\.(\w+):\s+(.+)$/';

    while (($line = fgets($file)) !== false && ($index < $searchParam['line_count'])) {
      $trimmedLine = trim($line);

      if ('' === $trimmedLine) {
        continue;
      }

      $isNewEntry = false;
      $parsedEntry = null;

      // Try standard format first
      if (preg_match($standardPattern, $trimmedLine, $matches)) {
        $metadata = $this->parseLogMetadata($matches[5] ?? '');

        $parsedEntry = [
          'datetime' => $matches[1],
          'level' => strtoupper($matches[2]),
          'channel' => $matches[3],
          'message' => $matches[4],
          'ip' => $metadata['ip'] ?? '',
          'user' => $metadata['user'] ?? '',
          'context' => $metadata['context'] ?? '',
          'extra' => $metadata['extra'] ?? '',
          'stacktrace' => '',
        ];
        $isNewEntry = true;
      }
      // Try ISO format (deprecation logs)
      elseif (preg_match($isoPattern, $trimmedLine, $matches)) {
        $datetime = $matches[2];
        $channel = $matches[3];
        $level = strtoupper($matches[4]);
        $rest = $matches[5];

        // Parse message and JSON metadata
        $parsed = $this->parseDeprecationLog($rest);

        $parsedEntry = [
          'datetime' => $datetime,
          'level' => $level,
          'channel' => $channel,
          'message' => $parsed['message'],
          'ip' => $parsed['ip'],
          'user' => $parsed['user'],
          'context' => $parsed['context'],
          'extra' => $parsed['extra'],
          'stacktrace' => '',
        ];
        $isNewEntry = true;
      }

      if ($isNewEntry && $parsedEntry) {
        // Save previous log entry if exists
        if (null !== $currentLogEntry) {
          $content[] = $currentLogEntry;
          ++$index;
        }
        $currentLogEntry = $parsedEntry;
      } else {
        // This is a continuation line (probably stack trace or multi-line message)
        if (null !== $currentLogEntry) {
          if (!empty($currentLogEntry['stacktrace'])) {
            $currentLogEntry['stacktrace'] .= "\n";
          }
          $currentLogEntry['stacktrace'] .= $trimmedLine;
        }
      }
    }

    // Add the last log entry if it exists
    if (null !== $currentLogEntry && $index < $searchParam['line_count']) {
      $content[] = $currentLogEntry;
    }

    fclose($file);

    // Reverse to show newest first
    return array_reverse($content);
  }

  private function parseDeprecationLog(string $logLine): array
  {
    $result = [
      'message' => '',
      'ip' => '',
      'user' => '',
      'context' => '',
      'extra' => '',
    ];

    // The line format is: Message {json1} {json2}
    // First extract the message part (everything before first {)
    if (preg_match('/^(.+?)(\s*\{.*)$/', $logLine, $matches)) {
      $result['message'] = trim($matches[1]);
      $jsonPart = $matches[2];

      // Extract all JSON objects
      preg_match_all('/\{(?:[^{}]|(?R))*\}/x', $jsonPart, $jsonMatches);

      $allJsonData = [];
      foreach ($jsonMatches[0] as $jsonStr) {
        $decoded = json_decode($jsonStr, true);
        if (is_array($decoded)) {
          $allJsonData = array_merge($allJsonData, $decoded);
        }
      }

      // Extract metadata
      if (isset($allJsonData['client_ip'])) {
        $result['ip'] = $allJsonData['client_ip'];
      }
      if (isset($allJsonData['session_user'])) {
        $result['user'] = $allJsonData['session_user'];
      }
      if (isset($allJsonData['exception'])) {
        $result['context'] = $allJsonData['exception'];
      }

      // Store full JSON as extra
      if (!empty($allJsonData)) {
        $result['extra'] = json_encode($allJsonData, JSON_PRETTY_PRINT);
      }
    } else {
      // No JSON found, entire line is message
      $result['message'] = $logLine;
    }

    return $result;
  }

  private function parseLogMetadata(string $metadata): array
  {
    $result = [
      'ip' => '',
      'user' => '',
      'context' => '',
      'extra' => '',
    ];

    if (empty($metadata)) {
      return $result;
    }

    // Extract ip=value
    if (preg_match('/ip=([^\s]+)/', $metadata, $matches)) {
      $result['ip'] = $matches[1];
    }

    // Extract user=value
    if (preg_match('/user=([^\s]+)/', $metadata, $matches)) {
      $result['user'] = $matches[1];
    }

    // Extract context {...}
    if (preg_match('/(\{[^}]*\})/', $metadata, $matches)) {
      $result['context'] = $matches[1];
    }

    // Store the full metadata as extra
    $result['extra'] = $metadata;

    return $result;
  }
}
