<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\CommandHelper;
use function Clue\StreamFilter\append;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Class CheckCodeStyleCommand
 * @package App\Catrobat\Commands
 */
class CheckCodeStyleCommand extends ContainerAwareCommand
{

  /**
   *
   */
  protected function configure()
  {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('phpcheckstyle')
      // the short description shown while running "php bin/console list"
      ->setDescription('checks the php coding style')
      // the full command description shown when running the command with
      // the "--help" option
      ->setHelp('This command allows you to check the php coding style. 
                  The report can be found in tests\style-report')
      ->addArgument('githash', InputArgument::OPTIONAL, 'The git hash of the git commit you want to check. Only modified fiels and newly added files in the src/ directory will be checked.');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    if ($input->getArgument('githash'))
    {
      $output->writeln("PHP code style checking running only for git commit: " . $input->getArgument('githash'));

      $command_git = "git diff-tree --no-commit-id --name-only -r " . $input->getArgument('githash');
      $process_git = new Process($command_git);
      $process_git->run();

      if (!$process_git->isSuccessful())
      {
        throw new ProcessFailedException($command_git);
      }

      $process_git_output = array_filter(explode("\n", $process_git->getOutput()), 'strlen');
      $git_files_regex = "/src\/.*\.php/";
      $git_files = [];

      foreach ($process_git_output as $entry)
      {
        if (preg_match($git_files_regex, $entry))
        {
          array_push($git_files, $entry);
        }
      }

      if (!count($git_files))
      {
        $output->writeln("Could not find changed *.php files in src/ dir. ABORTING!");

        return 0;
      }
      else
      {
        $command = "time php ~/Catroweb-Symfony/vendor/phpcheckstyle/phpcheckstyle/run.php --config ~/Catroweb-Symfony/tests/style-report/catroweb.xml --outdir ~/Catroweb-Symfony/tests/style-report";
        foreach ($git_files as $entry)
        {
          $command = $command . " --src " . $entry;
        }
      }
      $process = new Process($command);

      // Getting real time output
      $process->run(function ($type, $buffer) {
        echo $buffer;
      });

      return $process->getExitCode();

    }
    else
    {
      $output->writeln("PHP code style checking running. This may take a few moments...");
      $command = "time php ~/Catroweb-Symfony/vendor/phpcheckstyle/phpcheckstyle/run.php --src ~/Catroweb-Symfony/src/ --config ~/Catroweb-Symfony/tests/style-report/catroweb.xml --outdir ~/Catroweb-Symfony/tests/style-report";
      $process = new Process($command);

      // Getting real time output
      $process->run(function ($type, $buffer) {
        echo $buffer;
      });

      return $process->getExitCode();
    }
  }
}