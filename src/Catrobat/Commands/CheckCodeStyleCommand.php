<?php

namespace App\Catrobat\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class CheckCodeStyleCommand.
 */
class CheckCodeStyleCommand extends Command
{
  private ParameterBagInterface $parameter_bag;

  private string $root_dir;

  /**
   * CreateBackupCommand constructor.
   */
  public function __construct(ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
    $this->root_dir = $parameter_bag->get('kernel.root_dir');
  }

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
      ->addArgument('githash', InputArgument::OPTIONAL,
        'The git hash of the git commit you want to check.'
        .'Only modified fiels and newly added files in the src/ directory will be checked.'
      )
    ;
  }

  /**
   * @return int
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    if ($input->getArgument('githash'))
    {
      $output->writeln('PHP code style checking running only for git commit: '.$input->getArgument('githash'));

      $command_git = ['git', 'diff-tree', '--no-commit-id', '--name-only', '-r', $input->getArgument('githash')];
      $process_git = new Process($command_git);
      $process_git->run();

      if (!$process_git->isSuccessful())
      {
        throw new ProcessFailedException($process_git);
      }

      $process_git_output = array_filter(explode("\n", $process_git->getOutput()), 'strlen');
      $git_files_regex = '/src\\/.*\\.php/';
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
        $output->writeln('Could not find changed *.php files in src/ dir. ABORTING!');

        return 0;
      }

      $command = [
        'time',
        'php', 'vendor/phpcheckstyle/phpcheckstyle/run.php',
        '--config', 'tests/style-report/catroweb.xml',
        '--outdir', 'tests/style-report',
      ];
      foreach ($git_files as $entry)
      {
        $command = $command.' --src '.$entry;
      }

      $process = new Process($command);

      // Getting real time output
      $process->run(function ($type, $buffer)
      {
        echo $buffer;
      });

      return $process->getExitCode();
    }

    $output->writeln('PHP code style checking running. This may take a few moments...');
    $command = [
      'time',
      'php', 'vendor/phpcheckstyle/phpcheckstyle/run.php',
      '--src', 'src/',
      '--config', 'tests/style-report/catroweb.xml',
      '--outdir', 'tests/style-report',
    ];
    $process = new Process($command);

    // Getting real time output
    $process->run(function ($type, $buffer)
    {
      echo $buffer;
    });

    return $process->getExitCode();
  }
}
