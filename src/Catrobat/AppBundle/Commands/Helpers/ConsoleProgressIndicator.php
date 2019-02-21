<?php

namespace Catrobat\AppBundle\Commands\Helpers;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConsoleProgressIndicator
 * @package Catrobat\AppBundle\Commands\Helpers
 */
class ConsoleProgressIndicator
{
  /**
   * @var OutputInterface
   */
  private $console;
  /**
   * @var int
   */
  private $inline_count = 0;
  /**
   * @var int
   */
  private $inline_limit = 80;
  /**
   * @var int
   */
  private $line_count = 1;
  /**
   * @var array
   */
  private $error_array = [];
  /**
   * @var int
   */
  private $error_print_limit = 50;
  /**
   * @var bool
   */
  private $enable_error_file;
  /**
   * @var string
   */
  private $on_success_msg = '<info>.</info>';
  /**
   * @var string
   */
  private $on_failure_msg = '<error>F</error>';

  /**
   * ConsoleProgressIndicator constructor.
   *
   * @param OutputInterface $console
   * @param bool            $enable_error_file
   */
  public function __construct(OutputInterface $console, $enable_error_file = false)
  {
    $this->console = $console;
    $this->enable_error_file = $enable_error_file;
  }

  /**
   * @param string $msg
   */
  public function isSuccess($msg = '')
  {
    if ($msg == '')
    {
      $msg = $this->on_success_msg;
    }
    $this->console->write($msg);
    $this->inline_count += 1;
    $this->checkProgress();
  }

  /**
   * @param string $msg
   */
  public function isFailure($msg = '')
  {
    if ($msg == '')
    {
      $msg = $this->on_failure_msg;
    }
    $this->console->write($msg);
    $this->inline_count += 1;
    $this->checkProgress();
  }

  /**
   * @param $msg
   */
  public function isOther($msg)
  {
    $this->console->write($msg);
    $this->inline_count += count($msg);
    $this->checkProgress();
  }

  /**
   * @param $error
   */
  public function addError($error)
  {
    array_push($this->error_array, $error);
  }

  /**
   *
   */
  public function printErrors()
  {
    $this->console->writeln('');
    $error_count = count($this->error_array);

    if ($error_count == 0)
    {
      $this->console->writeln('Successfully executed command. There were no errors.');
    }
    else
    {
      $this->console->writeln('Command executed. There were ' . $error_count . ' errors.');
      $this->console->writeln('Errors happened with the following inputs:');

      for ($iter = 0; $iter < $error_count; $iter++)
      {
        if ($iter >= $this->error_print_limit)
        {
          $this->console->writeln('...');
          if ($this->enable_error_file)
          {
            $this->console->writeln('Couldn\'t print all errors. Will try to create an error file.');
            $this->createErrorFile();
          }
          break;
        }

        $this->console->write($this->error_array[$iter]);

        if (($iter + 1) != $error_count)
        {
          $this->console->write(', ');
        }
      }
    }
  }

  /**
   *
   */
  public function createErrorFile()
  {
    if ($this->enable_error_file)
    {
      $error_count = count($this->error_array);

      if ($error_count == 0)
      {
        $this->console->writeln('Don\'t want to create an empty file (error_array is empty)');

        return;
      }

      $date = getdate();

      $filename = 'ErrorFile' . $date['hours'] . $date['minutes'] . $date['seconds'];
      $file = fopen($filename . '.txt', 'w');

      fwrite($file, "ErrorFile\n");
      fwrite($file, 'Date: ' . $date['mday'] . '.' . $date['mon'] . '.' . $date['year'] . ' ' . $date['hours'] . ':' .
        $date['minutes'] . "\n\n");
      fwrite($file, "The following inputs resulted in errors:\n");

      for ($iter = 0; $iter < $error_count; $iter++)
      {
        fwrite($file, $this->error_array[$iter] . "\n");
      }

      fclose($file);

      $this->console->writeln('Error file ' . $filename . ' successfully created.');
    }
    else
    {
      $this->console->writeln('If you want to create an error file, you should enable the error file flag ;)');
    }
  }

  /**
   *
   */
  private function checkProgress()
  {
    if ($this->inline_count >= $this->inline_limit)
    {
      $number = $this->line_count * $this->inline_limit;
      $this->console->writeln(' ' . $number);
      $this->inline_count = 0;
      $this->line_count += 1;
    }
  }

  /**
   * @return OutputInterface
   */
  public function getConsole()
  {
    return $this->console;
  }

  /**
   * @param OutputInterface $console
   */
  public function setConsole($console)
  {
    $this->console = $console;
  }

  /**
   * @return int
   */
  public function getInlineCount()
  {
    return $this->inline_count;
  }

  /**
   * @return int
   */
  public function getInlineLimit()
  {
    return $this->inline_limit;
  }

  /**
   * @param int $inline_limit
   */
  public function setInlineLimit($inline_limit)
  {
    $this->inline_limit = $inline_limit;
  }

  /**
   * @return int
   */
  public function getLineCount()
  {
    return $this->line_count;
  }

  /**
   * @return array
   */
  public function getErrorArray()
  {
    return $this->error_array;
  }

  /**
   * @return boolean
   */
  public function isEnableErrorFile()
  {
    return $this->enable_error_file;
  }

  /**
   * @param boolean $enable_error_file
   */
  public function setEnableErrorFile($enable_error_file)
  {
    $this->enable_error_file = $enable_error_file;
  }

  /**
   * @return string
   */
  public function getOnSuccessMsg()
  {
    return $this->on_success_msg;
  }

  /**
   * @param string $on_success_msg
   */
  public function setOnSuccessMsg($on_success_msg)
  {
    $this->on_success_msg = $on_success_msg;
  }

  /**
   * @return string
   */
  public function getOnFailureMsg()
  {
    return $this->on_failure_msg;
  }

  /**
   * @param string $on_failure_msg
   */
  public function setOnFailureMsg($on_failure_msg)
  {
    $this->on_failure_msg = $on_failure_msg;
  }

  /**
   * @return int
   */
  public function getErrorPrintLimit()
  {
    return $this->error_print_limit;
  }

  /**
   * @param int $error_print_limit
   */
  public function setErrorPrintLimit($error_print_limit)
  {
    $this->error_print_limit = $error_print_limit;
  }
}