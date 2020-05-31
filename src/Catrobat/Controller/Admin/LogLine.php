<?php

namespace App\Catrobat\Controller\Admin;

class LogLine
{
  public string $date = '';

  public string $debug_code = '';

  public int $debug_level = 0;

  public string $msg = '';

  public function __construct(string $line = null)
  {
    if (null === $line)
    {
      $this->msg = 'No Logs with Loglevel';
      $this->debug_code = 'No search results';
    }
    else
    {
      $this->date = $this->getSubstring($line, ']', true);
      $line = substr($line, strlen($this->date) + 1);
      $this->debug_code = $this->getSubstring($line, ':');
      $line = substr($line, strlen($this->debug_code) + 2);
      $this->msg = $line;

      $this->debug_level = $this->getDebugLevel($this->debug_code);
    }
  }

  private function getSubstring(string $string, string $needle, bool $last_char = false): string
  {
    $pos = strpos($string, (string) $needle);

    if (false === $pos)
    {
      return '';
    }
    if ($last_char)
    {
      ++$pos;
    }

    return substr($string, 0, $pos);
  }

  private function getDebugLevel(string $string): int
  {
    $pos = strpos($string, '.');
    $extracted_string = substr($string, $pos + 1);

    switch ($extracted_string)
    {
      case 'INFO':
        $debug_level = LogsController::FILTER_LEVEL_INFO;
        break;
      case 'WARNING':
        $debug_level = LogsController::FILTER_LEVEL_WARNING;
        break;
      case 'ERROR':
        $debug_level = LogsController::FILTER_LEVEL_ERROR;
        break;
      case 'CRITICAL':
        $debug_level = LogsController::FILTER_LEVEL_CRITICAL;
        break;
      case 'NOTICE':
        $debug_level = LogsController::FILTER_LEVEL_NOTICE;
        break;
      case 'ALERT':
        $debug_level = LogsController::FILTER_LEVEL_ALERT;
        break;
      case 'EMERGENCY':
        $debug_level = LogsController::FILTER_LEVEL_EMERGENCY;
        break;
      case 'DEBUG':
        $debug_level = LogsController::FILTER_LEVEL_DEBUG;
        break;
      default:
        $debug_level = LogsController::FILTER_LEVEL_DEBUG;
    }

    return $debug_level;
  }
}
