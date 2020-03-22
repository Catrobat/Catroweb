<?php

namespace App\Catrobat\Commands\Helpers;

use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Class CommandHelper.
 */
class CommandHelper
{
  public static function emptyDirectory(string $directory, string $description = '', OutputInterface $output = null): bool
  {
    if ($output)
    {
      $output->write($description." ('".$directory."') ... ");
    }

    if ('' == $directory)
    {
      if ($output)
      {
        $output->writeln('failed');
      }

      return false;
    }

    $filesystem = new Filesystem();

    $finder = new Finder();
    $finder->in($directory)->depth(0);
    foreach ($finder as $file)
    {
      // skip folder in templates directory
      if (('screenshots' !== $file->getFilename()) && ('thumbnails' !== $file->getFilename()))
      {
        $filesystem->remove($file);
      }
    }

    if ($output)
    {
      $output->writeln('OK');
    }

    return true;
  }

  public static function createDirectory(string $directory, string $description, OutputInterface $output): void
  {
    $output->write($description." ('".$directory."') ... ");
    if ('' == $directory)
    {
      $output->writeln('failed');

      return;
    }

    $filesystem = new Filesystem();
    $filesystem->mkdir($directory);

    $output->writeln('OK');
  }

  /**
   * @throws Exception
   */
  public static function executeSymfonyCommand(string $command, Application $application, array $args,
                                               OutputInterface $output): void
  {
    $command = $application->find($command);
    $args['command'] = $command;
    $input = new ArrayInput($args);
    $command->run($input, $output);
  }

  public static function executeShellCommand(array $command, array $config, string $description = '',
                                             OutputInterface $output = null): bool
  {
    if ($output)
    {
      $output->write($description." ('".implode(' ', $command)."') ... ");
    }

    $process = new Process($command);

    if (isset($config['timeout']))
    {
      $process->setTimeout($config['timeout']);
    }

    $process->run();

    if ($process->isSuccessful())
    {
      if ($output)
      {
        $output->writeln('OK');
      }

      return true;
    }

    if ($output)
    {
      $output->writeln('failed! - Exit-Code: '.$process->getExitCode());
      $output->writeln('Error output: '.$process->getErrorOutput());
    }

    return false;
  }
}
