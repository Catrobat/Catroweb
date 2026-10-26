<?php

declare(strict_types=1);

namespace App\Project\CodeView;

use App\DB\Entity\Project\Project;
use App\Project\CatrobatFile\ExtractedFileRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Keeps each project's code export (CodeExportRunner) in a file and makes it when it is missing, when the project's
 * code.xml changed or when a newer export was deployed. An export that fails is kept too, so a project that cannot
 * be shown does not start Node on every view; a newer export tries it again.
 *
 * Views never wait for a newer export: they get the stored one and it is made again after the response
 * (kernel.terminate). Only a project without a stored export for its code.xml waits for Node on its first view. At
 * most SLOTS exports run at once on a server; a view that finds none free is told to try again, it does not wait.
 */
#[AsEventListener(event: KernelEvents::TERMINATE, method: 'refreshQueued')]
class CodeViewExportStore
{
  /** Node exports at once (each up to CodeExportRunner's 1 GB) next to PHP-FPM's workers. */
  public const int SLOTS = 2;

  /** @var array<string, Project> to export again after the response */
  private array $queue = [];

  public function __construct(
    private readonly CodeExportRunner $runner,
    private readonly ExtractedFileRepository $extracted_file_repository,
    private readonly LoggerInterface $logger,
    #[Autowire('%catrobat.code_view.dir%')]
    private readonly string $store_dir,
  ) {
  }

  /**
   * The project's export for a view: its code (see CodeExport in the player) or ['error' => why]; null without
   * code.xml. An outdated export is returned as it is and made again after the response.
   *
   * @return array<string, mixed>|null
   *
   * @throws CodeExportBusyException when it has to be made and SLOTS exports are running
   */
  public function get(Project $project): ?array
  {
    $xml = $this->codeXml($project);
    if (null === $xml) {
      return null;
    }

    $key = $this->key($xml);
    $stored = $this->read((string) $project->getId());
    if (null !== $stored && ($stored['code'] ?? null) === $key['code']) {
      if (!$this->isCurrent($stored, $key)) {
        $this->queue($project);
      }

      return $stored['data'];
    }

    return $this->make($project, $xml, $key, false);
  }

  /**
   * Makes the project's export unless it is current (backfill, after an upload); with $wait for a free slot.
   *
   * @return array<string, mixed>|null
   *
   * @throws CodeExportBusyException without $wait, when SLOTS exports are running
   */
  public function refresh(Project $project, bool $wait): ?array
  {
    $xml = $this->codeXml($project);

    return null === $xml ? null : $this->make($project, $xml, $this->key($xml), $wait);
  }

  /** Exports the project after the response (an upload, an outdated export). */
  public function queue(Project $project): void
  {
    $this->queue[(string) $project->getId()] = $project;
  }

  /**
   * kernel.terminate: makes the queued exports. Busy slots skip them; the next view queues them again.
   */
  public function refreshQueued(): void
  {
    $queue = $this->queue;
    $this->queue = [];
    foreach ($queue as $id => $project) {
      try {
        $this->refresh($project, false);
      } catch (CodeExportBusyException) {
        $this->logger->info('Code view of project '.$id.' not refreshed: all export slots busy');
      } catch (\Throwable $e) {
        $this->logger->error('Code view export after the response failed: '.$e->getMessage());
      }
    }
  }

  /**
   * @param array{code: string, export: string|null} $key
   *
   * @return array<string, mixed>
   */
  private function make(Project $project, string $xml, array $key, bool $wait): array
  {
    $id = (string) $project->getId();
    $this->path($id); // a project id, also for its lock's name
    (new Filesystem())->mkdir($this->store_dir);
    // One export per project at a time; whoever waited finds it made.
    $lock = $this->lock($id, $wait);
    if (null === $lock) {
      throw new CodeExportBusyException('The code view of project '.$id.' is being made');
    }

    try {
      $stored = $this->read($id);
      if (null !== $stored && $this->isCurrent($stored, $key)) {
        return $stored['data'];
      }

      if (null === $key['export']) {
        // No export deployed: an older one is better than none.
        return $stored['data'] ?? ['error' => 'The code export is not available'];
      }

      $slot = $this->slot($wait);
      if (null === $slot) {
        throw new CodeExportBusyException('All '.self::SLOTS.' code export slots are busy');
      }

      try {
        $data = $this->runner->export($xml);
      } finally {
        $this->unlock($slot);
      }

      if (isset($data['error'])) {
        $this->logger->info('No code view for project '.$id.': '.(is_string($data['error']) ? $data['error'] : ''));
        if (null !== $stored && ($stored['code'] ?? null) === $key['code'] && !isset($stored['data']['error'])) {
          return $stored['data']; // the newer export failed where the older one did not
        }
      }

      $this->write($id, ['code' => $key['code'], 'export' => $key['export'], 'data' => $data]);

      return $data;
    } finally {
      $this->unlock($lock);
    }
  }

  /**
   * A free export slot (a lock file of SLOTS), or null; with $wait it polls until one is free (the backfill, not views).
   *
   * @return resource|null
   */
  private function slot(bool $wait)
  {
    while (true) {
      for ($i = 0; $i < self::SLOTS; ++$i) {
        $slot = $this->lock('.slot-'.$i, false);
        if (null !== $slot) {
          return $slot;
        }
      }
      if (!$wait) {
        return null;
      }
      usleep(200_000);
    }
  }

  /**
   * @return resource|null
   */
  private function lock(string $name, bool $wait)
  {
    $lock = fopen(rtrim($this->store_dir, '/').'/'.$name.'.lock', 'c');
    if (false === $lock) {
      throw new \RuntimeException('Cannot create the code view lock '.$name);
    }
    if (!flock($lock, $wait ? LOCK_EX : LOCK_EX | LOCK_NB)) {
      fclose($lock);

      return null;
    }

    return $lock;
  }

  /**
   * @param resource $lock
   */
  private function unlock($lock): void
  {
    flock($lock, LOCK_UN);
    fclose($lock);
  }

  private function codeXml(Project $project): ?string
  {
    $extracted = $this->extracted_file_repository->loadProjectExtractedFile($project);
    $xml = null === $extracted ? null : $extracted->getPath().'code.xml';

    return null === $xml || '' === (string) $project->getId() || !is_file($xml) ? null : $xml;
  }

  /**
   * @return array{code: string, export: string|null}
   */
  private function key(string $xml): array
  {
    return ['code' => (string) sha1_file($xml), 'export' => $this->runner->getVersion()];
  }

  /**
   * @param array<string, mixed>                     $stored
   * @param array{code: string, export: string|null} $key
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
