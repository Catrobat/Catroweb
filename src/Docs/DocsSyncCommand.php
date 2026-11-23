<?php

declare(strict_types=1);

namespace App\Docs;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'catrobat:docs:sync', description: 'Import the user docs from the docs repo (DOCS_REPOSITORY at DOCS_REF)')]
class DocsSyncCommand extends Command
{
  public function __construct(
    private readonly DocsSync $sync,
    private readonly HttpClientInterface $http_client,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addArgument('source', InputArgument::OPTIONAL, 'A local checkout or .tar.gz to import instead of downloading the repo');
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $source = $input->getArgument('source');
    $filesystem = new Filesystem();
    $tarball = null;
    try {
      if (\is_string($source) && is_dir($source)) {
        $files = DocsSync::readDirectory($source);
      } else {
        if (!\is_string($source)) {
          // PharData recognises the archive by its extension.
          $tarball = $filesystem->tempnam(sys_get_temp_dir(), 'docs_', '.tar.gz');
          $response = $this->http_client->request('GET', $this->sync->tarballUrl(), ['timeout' => 60]);
          if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Docs download failed with HTTP '.$response->getStatusCode());
          }
          $handle = fopen($tarball, 'w');
          \assert(false !== $handle);
          foreach ($this->http_client->stream($response) as $chunk) {
            fwrite($handle, $chunk->getContent());
          }
          fclose($handle);
        }
        $files = DocsSync::readTarball($tarball ?? (string) $source);
      }
      $result = $this->sync->import($files);
    } finally {
      if (null !== $tarball) {
        $filesystem->remove($tarball);
      }
    }

    $output->writeln(\sprintf('Docs: %d created, %d updated, %d deleted.', $result['created'], $result['updated'], $result['deleted']));
    foreach ($result['skipped'] as $problem) {
      $output->writeln('Skipped '.$problem);
    }

    return Command::SUCCESS;
  }
}
