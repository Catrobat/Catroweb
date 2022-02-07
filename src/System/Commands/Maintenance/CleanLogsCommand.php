<?php

namespace App\System\Commands\Maintenance;

use App\Storage\FileHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CleanLogsCommand extends Command
{
  protected static $defaultName = 'catrobat:clean:logs';
  private OutputInterface $output;

  private ParameterBagInterface $parameter_bag;

  public function __construct(ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
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
    $log_dir = strval($this->parameter_bag->get('catrobat.logs.dir'));
    try {
      FileHelper::emptyDirectory($log_dir);
    } catch (Exception $e) {
      $output->writeln('Clearing log files failed with code '.$e->getCode());
    }

    return 0;
  }
}
