<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\ImportProjects;

use App\System\Commands\ImportProjects\ImportProjectsFromShare;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @internal
 */
#[CoversClass(ImportProjectsFromShare::class)]
class ImportProjectsFromShareTest extends TestCase
{
  public function testPagesThroughTheV2ApiAndHandsTheFilesToTheImporter(): void
  {
    $requested = [];
    $http_client = new MockHttpClient(static function (string $method, string $url) use (&$requested): MockResponse {
      $requested[] = $url;
      if (str_contains($url, '/catrobat')) {
        return new MockResponse('zip-of-'.basename(dirname($url)));
      }

      $page = str_contains($url, 'cursor=c2')
        ? ['data' => [['id' => 'p3', 'download_url' => 'https://share.catrobat.org/api/projects/p3/catrobat']], 'next_cursor' => null, 'has_more' => false]
        : ['data' => [
          ['id' => 'p1', 'download_url' => 'https://share.catrobat.org/api/projects/p1/catrobat'],
          ['id' => 'p2', 'download_url' => 'https://share.catrobat.org/api/projects/p2/catrobat'],
        ], 'next_cursor' => 'c2', 'has_more' => true];

      return new MockResponse(json_encode($page, JSON_THROW_ON_ERROR));
    });

    $importer = new class extends Command {
      /** @var array<string, string> */
      public array $files = [];

      public function __construct()
      {
        parent::__construct('catrobat:import');
      }

      #[\Override]
      protected function configure(): void
      {
        $this->addArgument('directory', InputArgument::REQUIRED)
          ->addArgument('user', InputArgument::REQUIRED)
          ->addOption('remix-layout', null, InputOption::VALUE_REQUIRED)
        ;
      }

      #[\Override]
      protected function execute(InputInterface $input, OutputInterface $output): int
      {
        foreach (glob($input->getArgument('directory').'*.catrobat') ?: [] as $file) {
          $this->files[basename($file)] = (string) file_get_contents($file);
        }

        return 0;
      }
    };

    $application = new Application();
    $application->addCommand($importer);
    $application->addCommand(new ImportProjectsFromShare($http_client));

    $tester = new CommandTester($application->find('catrobat:import:share'));
    $tester->execute(['--limit' => '3', '--category' => 'random']);

    $this->assertSame(0, $tester->getStatusCode());
    $this->assertSame(['p1.catrobat' => 'zip-of-p1', 'p2.catrobat' => 'zip-of-p2', 'p3.catrobat' => 'zip-of-p3'], $importer->files);
    $this->assertStringStartsWith('https://share.catrobat.org/api/projects?', $requested[0]);
    $this->assertStringContainsString('category=random', $requested[0]);
    $this->assertStringContainsString('limit=3', $requested[0]);
    $this->assertStringContainsString('limit=1', $requested[3]);
    $this->assertStringContainsString('cursor=c2', $requested[3]);
  }
}
