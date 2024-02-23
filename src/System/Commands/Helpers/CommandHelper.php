<?php

namespace App\System\Commands\Helpers;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

class CommandHelper
{
  /**
   * @throws \Exception
   */
  public static function executeSymfonyCommand(string $command, Application $application, array $args,
    OutputInterface $output): int
  {
    $command = $application->find($command);
    $args['command'] = $command;
    $input = new ArrayInput($args);

    return $command->run($input, $output);
  }

  public static function executeShellCommand(array $command, array $config, string $description = '',
    ?OutputInterface $output = null, ?KernelInterface $kernel = null): ?int
  {
    if (null !== $output) {
      $output->write($description." ('".implode(' ', $command)."') ... ");
    }

    $app_env = $_ENV['APP_ENV'];
    $final_command = $command;
    if ('test' === $app_env) {
      $final_command[] = '--env=test';
    }

    $process = new Process($final_command, null, ['APP_ENV' => 'false', 'SYMFONY_DOTENV_VARS' => 'false']);
    if (!is_null($kernel)) {
      $process->setWorkingDirectory($kernel->getProjectDir());
    }

    if (isset($config['timeout'])) {
      $process->setTimeout($config['timeout']);
    }

    $process->run();

    if ($process->isSuccessful()) {
      if (null !== $output) {
        $output->writeln($process->getOutput());
        $output->writeln('OK');
      }

      return 0;
    }

    if (null !== $output) {
      $output->writeln($process->getOutput());
      $output->writeln('failed! - Exit-Code: '.$process->getExitCode());
      $output->writeln('Error output: '.$process->getErrorOutput());
    }

    return $process->getExitCode();
  }
}
