<?php

namespace App\Commands\Maintenance;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class CleanLogsCommand extends Command
{
  protected static $defaultName = 'catrobat:clean:logs';
  private OutputInterface $output;

  private ParameterBagInterface $parameter_bag;
  private Filesystem $fs;

  public function __construct(ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
    $this->fs = new Filesystem();
  }

  protected function configure(): void
  {
    $this->setName('catrobat:clean:logs')
      ->setDescription('Delete the log files')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->output = $output;
    $this->output->writeln('Deleting log files');
    $log_dir = $this->parameter_bag->get('catrobat.logs.dir');
    try
    {
      $objs = glob($log_dir.'/*');
      foreach ($objs as $obj)
      {
        $this->fs->remove($obj);
      }
    }
    catch (Exception $e)
    {
      $output->writeln('Removing log files failed with code '.$e->getCode());
    }

    return 0;
  }
}
