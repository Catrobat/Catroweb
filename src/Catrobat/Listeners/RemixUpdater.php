<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramAfterInsertEvent;
use App\Catrobat\Services\AsyncHttpClient;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RemixData;
use App\Entity\Program;
use App\Entity\RemixManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\Routing\RouterInterface;

class RemixUpdater
{
  /**
   * @var string
   */
  const MIGRATION_LOCK_FILE_NAME = 'CatrobatRemixMigration.lock';

  private RemixManager $remix_manager;

  private AsyncHttpClient $async_http_client;

  private RouterInterface $router;

  private string $migration_lock_file_path;

  public function __construct(RemixManager $remix_manager, AsyncHttpClient $async_http_client, RouterInterface $router,
                              string $kernel_root_dir)
  {
    $this->remix_manager = $remix_manager;
    $this->async_http_client = $async_http_client;
    $this->router = $router;
    $app_root_dir = $kernel_root_dir;
    $this->migration_lock_file_path = $app_root_dir.'/'.self::MIGRATION_LOCK_FILE_NAME;
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function onProgramAfterInsert(ProgramAfterInsertEvent $event): void
  {
    $this->update($event->getExtractedFile(), $event->getProgramEntity());
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws Exception
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
    if (version_compare($file->getLanguageVersion(), '0.992', '<=') && (count($remixes_data) >= 2))
    {
      $remixes_data = [];
      $remix_url_string = '';
    }

    if (count($scratch_remixes_data) > 0)
    {
      $scratch_ids = array_map(fn (RemixData $data) => $data->getProgramId(), $scratch_remixes_data);
      $existing_scratch_ids = $this->remix_manager->filterExistingScratchProgramIds($scratch_ids);
      $not_existing_scratch_ids = array_diff($scratch_ids, $existing_scratch_ids);
      $scratch_info_data = $this->async_http_client->fetchScratchProgramDetails($not_existing_scratch_ids);
    }

    if (!file_exists($this->migration_lock_file_path))
    {
      // TODO: make sure no inconsistencies (due to concurrency issues) can happen here!!
      $this->remix_manager->addScratchPrograms($scratch_info_data);
      $this->remix_manager->addRemixes($program, $remixes_data);
    }

    $program_xml_properties->header->remixOf = $remix_url_string;
    $program_xml_properties->header->url = $this->router->generate('program', ['id' => $program->getId(), 'flavor' => 'pocketcode']);
    $program_xml_properties->header->userHandle = $program->getUser()->getUsername();
    $file->saveProgramXmlProperties();
  }
}
