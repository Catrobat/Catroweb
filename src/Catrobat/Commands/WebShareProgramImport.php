<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\CommandHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class WebShareProgramImport.
 */
class WebShareProgramImport extends Command
{
  protected function configure()
  {
    $this->setName('catrobat:import:webshare')
      ->setDescription('Imports the specified amount of recent programs from share.catrob.at')
      ->addArgument('amount', InputArgument::REQUIRED,
        'The amount of recent programs that should be downloaded and imported')
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): void
  {
    $amount = $input->getArgument('amount');
    $temp_dir = sys_get_temp_dir().'/catrobat.program.import/';

    $filesystem = new Filesystem();
    $filesystem->remove($temp_dir);
    $filesystem->mkdir($temp_dir);
    $this->downloadPrograms($temp_dir, $output, $amount);
    CommandHelper::executeSymfonyCommand('catrobat:import', $this->getApplication(),
      ['directory' => $temp_dir, 'user' => 'catroweb'], $output);
    $filesystem->remove($temp_dir);
  }

  private function downloadPrograms(string $dir, OutputInterface $output, int $limit = 20): void
  {
    $server_json = json_decode(file_get_contents(
      'https://share.catrob.at/app/api/projects/recent.json?limit='.$limit), true);
    $base_url = $server_json['CatrobatInformation']['BaseUrl'];
    foreach ($server_json['CatrobatProjects'] as $program)
    {
      $url = $base_url.$program['DownloadUrl'];
      $name = $dir.$program['ProjectId'].'.catrobat';
      $output->writeln('Saving <'.$url.'> to <'.$name.'>');
      file_put_contents($name, file_get_contents($url));
    }
  }
}
