<?php

namespace App\Project\Remix;

use App\DB\Entity\Project\Program;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\Event\ProgramAfterInsertEvent;
use App\Project\Scratch\AsyncHttpClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

class RemixUpdaterEventSubscriber implements EventSubscriberInterface
{
  /**
   * @var string
   */
  final public const MIGRATION_LOCK_FILE_NAME = 'CatrobatRemixMigration.lock';

  private readonly string $migration_lock_file_path;

  public function __construct(private readonly RemixManager $remix_manager, private readonly AsyncHttpClient $async_http_client, private readonly RouterInterface $router,
    string $kernel_root_dir)
  {
    $app_root_dir = $kernel_root_dir;
    $this->migration_lock_file_path = $app_root_dir.'/'.self::MIGRATION_LOCK_FILE_NAME;
  }

  /**
   * @throws \Exception
   */
  public function onProgramAfterInsert(ProgramAfterInsertEvent $event): void
  {
    $this->update($event->getExtractedFile(), $event->getProgramEntity());
  }

  /**
   * @throws \Exception
   */
  public function update(ExtractedCatrobatFile $file, Program $program): void
  {
    $remixes_data = $file->getRemixesData(
      $program->getId(),
      $program->isInitialVersion(),
      $this->remix_manager->getProgramRepository()
    );
    $scratch_remixes_data = array_filter($remixes_data, fn (RemixData $remix_data) => $remix_data->isScratchProgram());
    $scratch_info_data = [];
    $program_xml_properties = $file->getProgramXmlProperties();
    $remix_url_string = $file->getRemixUrlsString();

    // ignore remix parents of old Catrobat programs, Catroid had a bug until Catrobat Language Version 0.992
    // For more details on this, please have a look at: https://jira.catrob.at/browse/CAT-2149
    if (version_compare($file->getLanguageVersion(), '0.992', '<=') && (count($remixes_data) >= 2)) {
      $remixes_data = [];
      $remix_url_string = '';
    }

    if (count($scratch_remixes_data) > 0) {
      $scratch_ids = array_map(fn (RemixData $data) => $data->getProgramId(), $scratch_remixes_data);
      $existing_scratch_ids = $this->remix_manager->filterExistingScratchProgramIds($scratch_ids);
      $not_existing_scratch_ids = array_diff($scratch_ids, $existing_scratch_ids);
      $scratch_info_data = $this->async_http_client->fetchScratchProgramDetails($not_existing_scratch_ids);
    }

    if (!file_exists($this->migration_lock_file_path)) {
      // TODO: make sure no inconsistencies (due to concurrency issues) can happen here!!
      $this->remix_manager->addScratchPrograms($scratch_info_data);
      $this->remix_manager->addRemixes($program, $remixes_data);
    }

    $program_xml_properties->header->remixOf = $remix_url_string;
    $program_xml_properties->header->url = $this->router->generate('program', ['id' => $program->getId(), 'theme' => 'pocketcode']);
    $program_xml_properties->header->userHandle = $program->getUser()->getUserIdentifier();
    $file->saveProgramXmlProperties();
  }

  public static function getSubscribedEvents(): array
  {
    return [ProgramAfterInsertEvent::class => 'onProgramAfterInsert'];
  }
}
