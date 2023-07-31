<?php

namespace App\System\Commands\Maintenance;

use App\Storage\FileHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CleanLogsCommand extends Command
{
  protected static $defaultDescription = 'Delete the log files';

  public function __construct(private readonly ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this->setName('catrobat:clean:logs');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output1 = $output;
    $output1->writeln('Deleting log files');
    $log_dir = strval($this->parameter_bag->get('catrobat.logs.dir'));
    try {
      FileHelper::emptyDirectory($log_dir);
    } catch (\Exception $e) {
      $output->writeln('Clearing log files failed with code '.$e->getCode());
    }

    return \Symfony\Component\Console\Command\Command::SUCCESS;
  }
}
