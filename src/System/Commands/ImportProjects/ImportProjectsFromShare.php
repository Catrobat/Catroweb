<?php

declare(strict_types=1);

namespace App\System\Commands\ImportProjects;

use App\Storage\FileHelper;
use App\System\Commands\Helpers\CommandHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'catrobat:import:share', description: 'Imports the specified amount of recent projects from share.catrob.at')]
class ImportProjectsFromShare extends Command
{
  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('limit', 'l', InputOption::VALUE_REQUIRED,
        'The limit of projects that should be downloaded and imported',
        '20')
      ->addOption('category', 'c', InputOption::VALUE_REQUIRED,
        'Downloading projects of a specific category (random, recent)',
        'recent')
      ->addOption('user', 'u', InputOption::VALUE_REQUIRED,
        'User who should be the owner of the projects',
        'catroweb')
      ->addOption('remix-layout', null, InputOption::VALUE_REQUIRED,
        'Generates remix graph based on given layout',
        ProgramImportCommand::REMIX_GRAPH_NO_LAYOUT)
    ;
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $number_of_projects_to_download = intval($input->getOption('limit'));
    $project_owner = (string) $input->getOption('user');
    $download_url = $this->getDownloadUrl((string) $input->getOption('category'));
    $remix_layout = intval($input->getOption('remix-layout'));

    $temp_dir = sys_get_temp_dir().'/catrobat.program.import/';
    FileHelper::removeDirectory($temp_dir);
    $filesystem = new Filesystem();
    $filesystem->mkdir($temp_dir);

    $this->downloadProjects($temp_dir, $number_of_projects_to_download, $download_url, $output);
    $this->importProjects($temp_dir, $project_owner, $remix_layout, $output);

    return 0;
  }

  private function getDownloadUrl(string $category): string
  {
    return match ($category) {
      'random' => 'https://share.catrob.at/app/api/projects/randomProjects.json',
      default => 'https://share.catrob.at/app/api/projects/recent.json',
    };
  }

  private function downloadProjects(string $dir, int $limit, string $url, OutputInterface $output): void
  {
    $downloads_left = $limit;
    while ($downloads_left > 0) {
      $projects = @file_get_contents($url.'?limit='.$downloads_left);
      if (false === $projects) {
        --$downloads_left;
        continue;
      }

      $server_json = json_decode($projects, true, 512, JSON_THROW_ON_ERROR);

      $base_url = $server_json['CatrobatInformation']['BaseUrl'];
      foreach ($server_json['CatrobatProjects'] as $program) {
        $project_url = $base_url.$program['DownloadUrl'];
        $name = $dir.$program['ProjectId'].'.catrobat';
        $output->writeln('Saving <'.$project_url.'> to <'.$name.'>');
        try {
          $project_json = @file_get_contents($project_url);
          file_put_contents($name, $project_json);
          if (false === $project_json) {
            --$downloads_left;
            continue;
          }

          --$downloads_left;
        } catch (\Exception $e) {
          $output->writeln('File <'.$project_url.'> download failed');
          $output->writeln('Error code: '.$e->getCode());
          $output->writeln('Error message: '.$e->getMessage());
          $output->writeln('continuing...');
        }
      }
    }
  }

  /**
   * @throws \Exception
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

    FileHelper::removeDirectory($import_dir);
  }
}
