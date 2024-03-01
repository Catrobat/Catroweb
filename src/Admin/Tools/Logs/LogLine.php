<?php

namespace App\Admin\Tools\Logs;

use App\Admin\Tools\Logs\Controller\LogsController;

class LogLine
{
  private string $date = '';

  private string $debug_code = '';

  private int $debug_level = 0;

  private string $msg = '';

  public function __construct(?string $line = null)
  {
    if (null === $line) {
      $this->setMsg('No Logs with Loglevel');
      $this->setDebugCode('No search results');
    } else {
      $this->setDate($this->getSubstring($line, ']', true));
      $line = substr($line, strlen($this->getDate()) + 1);
      $this->setDebugCode($this->getSubstring($line, ':'));
      $line = substr($line, strlen($this->getDebugCode()) + 2);
      $this->setMsg($line);

      $this->setDebugLevel($this->getDebugLevelByString($this->getDebugCode()));
    }
  }

  public function getDebugLevel(): int
  {
    return $this->debug_level;
  }

  public function setDebugLevel(int $debug_level): void
  {
    $this->debug_level = $debug_level;
  }

  public function getDebugCode(): string
  {
    return $this->debug_code;
  }

  public function setDebugCode(string $debug_code): void
  {
    $this->debug_code = $debug_code;
  }

  public function getDate(): string
  {
    return $this->date;
  }

  public function setDate(string $date): void
  {
    $this->date = $date;
  }

  public function getMsg(): string
  {
    return $this->msg;
  }

  public function setMsg(string $msg): void
  {
    $this->msg = $msg;
  }

  private function getSubstring(string $string, string $needle, bool $last_char = false): string
  {
    $pos = strpos($string, $needle);

    if (false === $pos) {
      return '';
    }
    if ($last_char) {
      ++$pos;
    }

    return substr($string, 0, $pos);
  }

  private function getDebugLevelByString(string $string): int
  {
    $pos = strpos($string, '.');
    $extracted_string = substr($string, $pos + 1);

    switch ($extracted_string) {
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
