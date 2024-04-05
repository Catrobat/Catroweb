<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'catrobat:clean:compressed', description: 'Removes all compressed project data.')]
class CleanCompressedProjectsCommand extends Command
{
  private readonly ?string $compressed_path;

  public function __construct(ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->compressed_path = (string) $parameter_bag->get('catrobat.file.storage.dir');
    if (!$this->compressed_path) {
      throw new \Exception('Invalid extract path given');
    }
  }

  /**
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $files = glob($this->compressed_path.'*'); // get all file names
    foreach ($files as $file) {
      if (is_file($file)) {
        unlink($file);
      }
    }

    return 0;
  }
}
