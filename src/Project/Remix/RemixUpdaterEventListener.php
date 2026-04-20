<?php

declare(strict_types=1);

namespace App\Project\Remix;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Project;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\Event\ProjectAfterInsertEvent;
use App\Project\Scratch\AsyncHttpClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\RouterInterface;

#[AsEventListener(event: ProjectAfterInsertEvent::class, method: 'onProjectAfterInsert')]
class RemixUpdaterEventListener
{
  public function __construct(
    private readonly RemixManager $remix_manager,
    private readonly AsyncHttpClient $async_http_client,
    private readonly RouterInterface $router,
    private readonly LoggerInterface $logger,
    #[Autowire('%kernel.project_dir%/CatrobatRemixMigration.lock')]
    private readonly string $migration_lock_file_path,
  ) {
  }

  /**
   * @throws \Exception
   */
  public function onProjectAfterInsert(ProjectAfterInsertEvent $event): void
  {
    try {
      $this->update($event->getExtractedFile(), $event->getProjectEntity());
    } catch (\Throwable $e) {
      $this->logger->error('RemixUpdaterEventListener failed: '.$e->getMessage());
    }
  }

  /**
   * @throws \Exception
   *
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function update(ExtractedCatrobatFile $file, Project $project): void
  {
    $remixes_data = $file->getRemixesData(
      $project->getId(),
      $project->isInitialVersion(),
      $this->remix_manager->getProjectRepository()
    );
    $scratch_remixes_data = array_filter($remixes_data, static fn (RemixData $remix_data): bool => $remix_data->isScratchProject());
    $scratch_info_data = [];
    $project_xml_properties = $file->getProjectXmlProperties();
    $remix_url_string = $file->getRemixUrlsString();

    // ignore remix parents of old Catrobat projects, Catroid had a bug until Catrobat Language Version 0.992
    // For more details on this, please have a look at: https://jira.catrob.at/browse/CAT-2149
    if (version_compare($file->getLanguageVersion(), '0.992', '<=') && (count($remixes_data) >= 2)) {
      $remixes_data = [];
      $remix_url_string = '';
    }

    if ([] !== $scratch_remixes_data) {
      $scratch_ids = array_map(static fn (RemixData $data): string => $data->getProjectId(), $scratch_remixes_data);
      $existing_scratch_ids = $this->remix_manager->filterExistingScratchProjectIds($scratch_ids);
      $not_existing_scratch_ids = array_diff($scratch_ids, $existing_scratch_ids);
      $scratch_info_data = $this->async_http_client->fetchScratchProjectDetails($not_existing_scratch_ids);
    }

    if (!file_exists($this->migration_lock_file_path)) {
      $this->withRemixUpdateLock(function () use ($scratch_info_data, $project, $remixes_data): void {
        if (file_exists($this->migration_lock_file_path)) {
          return;
        }

        $this->remix_manager->addScratchProjects($scratch_info_data);
        $this->remix_manager->addRemixes($project, $remixes_data);
      });
    }

    $project_xml_properties->header->remixOf = $remix_url_string;
    $project_xml_properties->header->url = $this->router->generate('project', ['id' => $project->getId(), 'theme' => Flavor::POCKETCODE]);
    $project_xml_properties->header->userHandle = $project->getUser()->getUsername();
    $file->saveProjectXmlProperties();
  }

  /**
   * Serializes remix writes to avoid races when multiple uploads are processed in parallel.
   */
  private function withRemixUpdateLock(callable $update_callback): void
  {
    $lock_file_path = sys_get_temp_dir().'/catrobat-remix-updater-'.md5($this->migration_lock_file_path).'.lock';
    $lock_file = fopen($lock_file_path, 'c+');
    if (false === $lock_file) {
      throw new \RuntimeException('Could not open lock file: '.$lock_file_path);
    }

    try {
      if (!flock($lock_file, LOCK_EX)) {
        throw new \RuntimeException('Could not acquire lock file: '.$lock_file_path);
      }

      $update_callback();
    } finally {
      flock($lock_file, LOCK_UN);
      fclose($lock_file);
    }
  }
}
