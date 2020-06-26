<?php

namespace App\Commands\ImportProjects;

use App\Commands\Helpers\CommandHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImportProjectsFromShare extends Command
{
  protected static $defaultName = 'catrobat:import:share';

  protected function configure(): void
  {
    $this->setName('catrobat:import:webshare')
      ->setDescription('Imports the specified amount of recent programs from share.catrob.at')
      ->addOption('limit', 'l', InputOption::VALUE_REQUIRED,
        'The limit of projects that should be downloaded and imported',
      20)
      ->addOption('category', 'c', InputOption::VALUE_REQUIRED,
        'Downloading projects of a specific category (random, recent)',
        'random')
      ->addOption('user', 'u', InputOption::VALUE_REQUIRED,
        'User who should be the owner of the projects',
        'catroweb')
      ->addOption('remix-layout', null, InputOption::VALUE_REQUIRED,
        'Generates remix graph based on given layout',
        ProgramImportCommand::REMIX_GRAPH_NO_LAYOUT)
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $number_of_projects_to_download = intval($input->getOption('limit'));
    $project_owner = (string) $input->getOption('user');
    $download_url = $this->getDownloadUrl((string) $input->getOption('category'));
    $remix_layout = intval($input->getOption('remix-layout'));

    $temp_dir = sys_get_temp_dir().'/catrobat.program.import/';
    $filesystem = new Filesystem();
    $filesystem->remove($temp_dir);
    $filesystem->mkdir($temp_dir);

    $this->downloadProjects($temp_dir, $number_of_projects_to_download, $download_url, $output);
    $this->importProjects($temp_dir, $project_owner, $remix_layout, $output);

    return 0;
  }

  private function getDownloadUrl(string $category): string
  {
    switch ($category)
    {
      case 'recent':
        return 'https://share.catrob.at/app/api/projects/recent.json';
      case 'random':
      default:
        return 'https://share.catrob.at/app/api/projects/randomProjects.json';
    }
  }

  private function downloadProjects(string $dir, int $limit, string $url, OutputInterface $output): void
  {
    $downloads_left = $limit;
    while ($downloads_left > 0)
    {
      $server_json = json_decode(file_get_contents($url.'?limit='.$downloads_left), true);
      $base_url = $server_json['CatrobatInformation']['BaseUrl'];
      foreach ($server_json['CatrobatProjects'] as $program)
      {
        $project_url = $base_url.$program['DownloadUrl'];
        $name = $dir.$program['ProjectId'].'.catrobat';
        $output->writeln('Saving <'.$project_url.'> to <'.$name.'>');
        try
        {
          file_put_contents($name, file_get_contents($project_url));
          --$downloads_left;
        }
        catch (Exception $e)
        {
          $output->writeln('File <'.$project_url.'> download failed');
          $output->writeln('Error code: '.$e->getCode());
          $output->writeln('Error message: '.$e->getMessage());
          $output->writeln('continuing...');
        }
      }
    }
  }

  /**
   * @throws Exception
   */
  private function importProjects(string $import_dir, string $username, int $remix_layout, OutputInterface $output): void
  {
    CommandHelper::executeSymfonyCommand('catrobat:import', $this->getApplication(),
      [
        'directory' => $import_dir,
        'user' => $username,
      ],
      new NullOutput()
    );
    CommandHelper::executeSymfonyCommand('catrobat:import', $this->getApplication(),
      [
        'directory' => $import_dir,
        'user' => $username,
        '--remix-layout' => $remix_layout,
      ],
      $output);

    $filesystem = new Filesystem();
    $filesystem->remove($import_dir);
  }
}
