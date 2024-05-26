<?php

declare(strict_types=1);

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

    return match ($extracted_string) {
      'INFO' => LogsController::FILTER_LEVEL_INFO,
      'WARNING' => LogsController::FILTER_LEVEL_WARNING,
      'ERROR' => LogsController::FILTER_LEVEL_ERROR,
      'CRITICAL' => LogsController::FILTER_LEVEL_CRITICAL,
      'NOTICE' => LogsController::FILTER_LEVEL_NOTICE,
      'ALERT' => LogsController::FILTER_LEVEL_ALERT,
      'EMERGENCY' => LogsController::FILTER_LEVEL_EMERGENCY,
      'DEBUG' => LogsController::FILTER_LEVEL_DEBUG,
      default => LogsController::FILTER_LEVEL_DEBUG,
    };
  }
}
