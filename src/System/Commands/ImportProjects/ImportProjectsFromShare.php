<?php

declare(strict_types=1);

namespace App\System\Commands\ImportProjects;

use App\Storage\FileHelper;
use App\System\Commands\Helpers\CommandHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'catrobat:import:share', description: 'Imports the specified amount of recent projects from share.catrobat.org')]
class ImportProjectsFromShare extends Command
{
  private const string PROJECTS_API_URL = 'https://share.catrobat.org/api/projects';

  public function __construct(
    private readonly HttpClientInterface $http_client,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('limit', 'l', InputOption::VALUE_REQUIRED,
        'The limit of projects that should be downloaded and imported',
        '20')
      ->addOption('category', 'c', InputOption::VALUE_REQUIRED,
        'Downloading projects of a specific category of GET /api/projects (e.g. recent, random, most_downloaded)',
        'recent')
      ->addOption('user', 'u', InputOption::VALUE_REQUIRED,
        'User who should be the owner of the projects',
        'catroweb')
      ->addOption('remix-layout', null, InputOption::VALUE_REQUIRED,
        'Generates remix graph based on given layout',
        ProjectImportCommand::REMIX_GRAPH_NO_LAYOUT)
    ;
  }

  /**
   * @throws \Exception
   * @throws ExceptionInterface
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $number_of_projects_to_download = intval($input->getOption('limit'));
    $project_owner = (string) $input->getOption('user');
    $category = (string) $input->getOption('category');
    $remix_layout = intval($input->getOption('remix-layout'));

    $temp_dir = sys_get_temp_dir().'/catrobat.program.import/';
    FileHelper::removeDirectory($temp_dir);
    $filesystem = new Filesystem();
    $filesystem->mkdir($temp_dir);

    $this->downloadProjects($temp_dir, $number_of_projects_to_download, $category, $output);
    $this->importProjects($temp_dir, $project_owner, $remix_layout, $output);

    return 0;
  }

  /**
   * Pages through GET /api/projects of the share and saves each project's .catrobat file into $dir.
   */
  private function downloadProjects(string $dir, int $limit, string $category, OutputInterface $output): void
  {
    $cursor = null;
    while ($limit > 0) {
      try {
        $page = $this->http_client->request('GET', self::PROJECTS_API_URL, ['query' => array_filter([
          'category' => $category,
          'limit' => $limit,
          'attributes' => 'id,download_url',
          'cursor' => $cursor,
        ], static fn (mixed $value): bool => null !== $value)])->toArray();
      } catch (HttpClientExceptionInterface $e) {
        $output->writeln('<error>Listing projects from <'.self::PROJECTS_API_URL.'> failed: '.$e->getMessage().'</error>');

        return;
      }

      foreach ($page['data'] ?? [] as $project) {
        if ($limit <= 0) {
          return;
        }

        $project_url = (string) $project['download_url'];
        $name = $dir.basename((string) $project['id']).'.catrobat';
        $output->writeln('Saving <'.$project_url.'> to <'.$name.'>');
        try {
          file_put_contents($name, $this->http_client->request('GET', $project_url)->getContent());
          --$limit;
        } catch (HttpClientExceptionInterface $e) {
          $output->writeln('File <'.$project_url.'> download failed: '.$e->getMessage().', continuing...');
        }
      }

      $cursor = $page['next_cursor'] ?? null;
      if (true !== ($page['has_more'] ?? false) || !is_string($cursor)) {
        return;
      }
    }
  }

  /**
   * @throws ExceptionInterface
   */
  private function importProjects(string $import_dir, string $username, int $remix_layout, OutputInterface $output): void
  {
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
