<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\Storage\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'catrobat:clean:logs', description: 'Delete the log files')]
class CleanLogsCommand extends Command
{
  public function __construct(private readonly ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output1 = $output;
    $output1->writeln('Deleting log files');

    $log_dir = strval($this->parameter_bag->get('catrobat.logs.dir'));
    try {
      FileHelper::emptyDirectory($log_dir);
    } catch (\Exception $exception) {
      $output->writeln('Clearing log files failed with code '.$exception->getCode());
    }

    return 0;
  }
}
