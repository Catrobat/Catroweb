<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\CommandHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;


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
    ;
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int
   */
  protected function execute(InputInterface $input, OutputInterface $output)
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