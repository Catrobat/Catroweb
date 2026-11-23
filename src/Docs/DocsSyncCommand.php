<?php

declare(strict_types=1);

namespace App\Docs;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'catrobat:docs:sync', description: 'Import the user docs from the docs repo (DOCS_REPOSITORY at DOCS_REF)')]
class DocsSyncCommand extends Command
{
  public function __construct(
    private readonly DocsSync $sync,
    private readonly HttpClientInterface $http_client,
    private readonly LockFactory $locks,
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
    // One sync at a time, whether started by the cron task or by hand.
    $lock = $this->locks->createLock('docs-sync', 1800);
    if (!$lock->acquire()) {
      $output->writeln('A docs sync is already running.');

      return Command::SUCCESS;
    }
    try {
      return $this->sync($input, $output);
    } finally {
      $lock->release();
    }
  }

  private function sync(InputInterface $input, OutputInterface $output): int
  {
    $source = $input->getArgument('source');
    $filesystem = new Filesystem();
    $tarball = $unpacked = null;
    try {
      if (\is_string($source) && is_dir($source)) {
        $files = DocsSync::readDirectory($source);
      } else {
        if (!\is_string($source)) {
          $tarball = $filesystem->tempnam(sys_get_temp_dir(), 'docs_');
          $response = $this->http_client->request('GET', $this->sync->tarballUrl(), ['timeout' => 60]);
          if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Docs download failed with HTTP '.$response->getStatusCode());
          }
          $handle = fopen($tarball, 'w');
          \assert(false !== $handle);
          $bytes = 0;
          foreach ($this->http_client->stream($response) as $chunk) {
            $bytes += fwrite($handle, $chunk->getContent()) ?: 0;
            if ($bytes > $this->sync->max_download_bytes) {
              $response->cancel();
              fclose($handle);
              throw new \RuntimeException('The docs download is larger than '.$this->sync->max_download_bytes.' bytes.');
            }
          }
          fclose($handle);
        }
        $unpacked = $filesystem->tempnam(sys_get_temp_dir(), 'docs_');
        $filesystem->remove($unpacked);
        $filesystem->mkdir($unpacked);
        $files = DocsSync::readDirectory(DocsSync::extractTarball($tarball ?? (string) $source, $unpacked, $this->sync->max_unpacked_bytes));
      }
      $result = $this->sync->import($files['pages']); // refuses a broken archive before the media are touched
      $media = $this->sync->importMedia($files['media']);
    } finally {
      $filesystem->remove(array_filter([$tarball, $unpacked]));
    }

    $output->writeln(\sprintf('Docs: %d created, %d updated, %d deleted, %d media files.', $result['created'], $result['updated'], $result['deleted'], $media));
    foreach ($result['skipped'] as $problem) {
      $output->writeln('Skipped '.$problem);
    }

    return Command::SUCCESS;
  }
}
