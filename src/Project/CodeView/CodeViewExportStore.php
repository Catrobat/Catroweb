<?php

declare(strict_types=1);

namespace App\Project\CodeView;

use App\DB\Entity\Project\Project;
use App\Project\CatrobatFile\ExtractedFileRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Keeps each project's code export (CodeExportRunner) in a file and makes it when it is missing, when the project's
 * code.xml changed or when a newer export was deployed. An export that fails is kept too, so a project that cannot
 * be shown does not start Node on every view; a newer export tries it again.
 */
class CodeViewExportStore
{
  public function __construct(
    private readonly CodeExportRunner $runner,
    private readonly ExtractedFileRepository $extracted_file_repository,
    private readonly LoggerInterface $logger,
    #[Autowire('%catrobat.code_view.dir%')]
    private readonly string $store_dir,
  ) {
  }

  /**
   * The project's export: its code (see CodeExport in the player) or ['error' => why]; null without code.xml.
   *
   * @return array<string, mixed>|null
   */
  public function get(Project $project): ?array
  {
    $id = (string) $project->getId();
    $extracted = $this->extracted_file_repository->loadProjectExtractedFile($project);
    $xml = null === $extracted ? null : $extracted->getPath().'code.xml';
    if (null === $xml || '' === $id || !is_file($xml)) {
      return null;
    }

    $key = ['code' => (string) sha1_file($xml), 'export' => $this->runner->getVersion()];
    $stored = $this->read($id);
    if (null !== $stored && $this->isCurrent($stored, $key)) {
      return $stored['data'];
    }

    // One export per project at a time; whoever waited finds it made.
    (new Filesystem())->mkdir($this->store_dir);
    $lock = fopen($this->path($id).'.lock', 'c');
    if (false === $lock) {
      return $stored['data'] ?? null;
    }

    try {
      flock($lock, LOCK_EX);
      $stored = $this->read($id);
      if (null !== $stored && $this->isCurrent($stored, $key)) {
        return $stored['data'];
      }

      if (null === $key['export']) {
        // No export deployed: an older one is better than none.
        return $stored['data'] ?? ['error' => 'The code export is not available'];
      }

      $data = $this->runner->export($xml);
      if (isset($data['error'])) {
        $this->logger->info('No code view for project '.$id.': '.(is_string($data['error']) ? $data['error'] : ''));
        if (null !== $stored && ($stored['code'] ?? null) === $key['code'] && !isset($stored['data']['error'])) {
          return $stored['data']; // the newer export failed where the older one did not
        }
      }

      $this->write($id, ['code' => $key['code'], 'export' => $key['export'], 'data' => $data]);

      return $data;
    } finally {
      flock($lock, LOCK_UN);
      fclose($lock);
    }
  }

  /**
   * @param array<string, mixed>      $stored
   * @param array<string, mixed|null> $key
   */
  private function isCurrent(array $stored, array $key): bool
  {
    return ($stored['code'] ?? null) === $key['code']
      && (null === $key['export'] || ($stored['export'] ?? null) === $key['export']);
  }

  /**
   * @return array<string, mixed>|null with 'data' an array
   */
  private function read(string $id): ?array
  {
    $file = $this->path($id);
    if (!is_file($file)) {
      return null;
    }

    try {
      $stored = json_decode((string) file_get_contents($file), true, 100_000, JSON_THROW_ON_ERROR);
    } catch (\JsonException) {
      return null;
    }

    return is_array($stored) && is_array($stored['data'] ?? null) ? $stored : null;
  }

  /**
   * @param array<string, mixed> $stored
   */
  private function write(string $id, array $stored): void
  {
    try {
      (new Filesystem())->dumpFile($this->path($id), json_encode($stored, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE, 100_000));
    } catch (\Throwable $e) {
      $this->logger->error('Could not store the code view of project '.$id.': '.$e->getMessage());
    }
  }

  private function path(string $id): string
  {
    if (1 !== preg_match('/^[A-Za-z0-9-]+$/', $id)) {
      throw new \InvalidArgumentException('Not a project id: '.$id);
    }

    return rtrim($this->store_dir, '/').'/'.$id.'.json';
  }
}
