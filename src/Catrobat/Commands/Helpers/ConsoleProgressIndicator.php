<?php

namespace App\Catrobat\Commands\Helpers;

use Symfony\Component\Console\Output\OutputInterface;

class ConsoleProgressIndicator
{
  private OutputInterface $console;

  private int $inline_count = 0;

  private int $inline_limit = 80;

  private int $line_count = 1;

  private array $error_array = [];

  private int $error_print_limit = 50;

  private bool $enable_error_file;

  private string $on_success_msg = '<info>.</info>';

  private string $on_failure_msg = '<error>F</error>';

  public function __construct(OutputInterface $console, bool $enable_error_file = false)
  {
    $this->console = $console;
    $this->enable_error_file = $enable_error_file;
  }

  public function isSuccess(string $msg = ''): void
  {
    if ('' == $msg)
    {
      $msg = $this->on_success_msg;
    }
    $this->console->write($msg);
    ++$this->inline_count;
    $this->checkProgress();
  }

  public function isFailure(string $msg = ''): void
  {
    if ('' == $msg)
    {
      $msg = $this->on_failure_msg;
    }
    $this->console->write($msg);
    ++$this->inline_count;
    $this->checkProgress();
  }

  /**
   * @param mixed $msg
   */
  public function isOther($msg): void
  {
    $this->console->write($msg);
    $this->inline_count += is_countable($msg) ? count($msg) : 0;
    $this->checkProgress();
  }

  /**
   * @param mixed $error
   */
  public function addError($error): void
  {
    $this->error_array[] = $error;
  }

  public function printErrors(): void
  {
    $this->console->writeln('');
    $error_count = count($this->error_array);

    if (0 == $error_count)
    {
      $this->console->writeln('Successfully executed command. There were no errors.');
    }
    else
    {
      $this->console->writeln('Command executed. There were '.$error_count.' errors.');
      $this->console->writeln('Errors happened with the following inputs:');

      for ($iter = 0; $iter < $error_count; ++$iter)
      {
        if ($iter >= $this->error_print_limit)
        {
          $this->console->writeln('...');
          if ($this->enable_error_file)
          {
            $this->console->writeln("Couldn't print all errors. Will try to create an error file.");
            $this->createErrorFile();
          }
          break;
        }

        $this->console->write($this->error_array[$iter]);

        if ($iter + 1 !== $error_count)
        {
          $this->console->write(', ');
        }
      }
    }
  }

  public function createErrorFile(): void
  {
    if ($this->enable_error_file)
    {
      $error_count = count($this->error_array);

      if (0 == $error_count)
      {
        $this->console->writeln("Don't want to create an empty file (error_array is empty)");

        return;
      }

      $date = getdate();

      $filename = 'ErrorFile'.$date['hours'].$date['minutes'].$date['seconds'];
      $file = fopen($filename.'.txt', 'w');

      fwrite($file, "ErrorFile\n");
      fwrite($file, 'Date: '.$date['mday'].'.'.$date['mon'].'.'.$date['year'].' '.$date['hours'].':'.
        $date['minutes']."\n\n");
      fwrite($file, "The following inputs resulted in errors:\n");

      for ($iter = 0; $iter < $error_count; ++$iter)
      {
        fwrite($file, $this->error_array[$iter]."\n");
      }

      fclose($file);

      $this->console->writeln('Error file '.$filename.' successfully created.');
    }
    else
    {
      $this->console->writeln('If you want to create an error file, you should enable the error file flag ;)');
    }
  }

  public function getConsole(): OutputInterface
  {
    return $this->console;
  }

  public function setConsole(OutputInterface $console): void
  {
    $this->console = $console;
  }

  public function getInlineCount(): int
  {
    return $this->inline_count;
  }

  public function getInlineLimit(): int
  {
    return $this->inline_limit;
  }

  public function setInlineLimit(int $inline_limit): void
  {
    $this->inline_limit = $inline_limit;
  }

  public function getLineCount(): int
  {
    return $this->line_count;
  }

  public function getErrorArray(): array
  {
    return $this->error_array;
  }

  public function isEnableErrorFile(): bool
  {
    return $this->enable_error_file;
  }

  public function setEnableErrorFile(bool $enable_error_file): void
  {
    $this->enable_error_file = $enable_error_file;
  }

  public function getOnSuccessMsg(): string
  {
    return $this->on_success_msg;
  }

  public function setOnSuccessMsg(string $on_success_msg): void
  {
    $this->on_success_msg = $on_success_msg;
  }

  public function getOnFailureMsg(): string
  {
    return $this->on_failure_msg;
  }

  public function setOnFailureMsg(string $on_failure_msg): void
  {
    $this->on_failure_msg = $on_failure_msg;
  }

  public function getErrorPrintLimit(): int
  {
    return $this->error_print_limit;
  }

  public function setErrorPrintLimit(int $error_print_limit): void
  {
    $this->error_print_limit = $error_print_limit;
  }

  private function checkProgress(): void
  {
    if ($this->inline_count >= $this->inline_limit)
    {
      $number = $this->line_count * $this->inline_limit;
      $this->console->writeln(' '.$number);
      $this->inline_count = 0;
      ++$this->line_count;
    }
  }
}
