<?php

namespace App\Catrobat\Listeners;

use App\Entity\Program;
use App\Entity\RemixManager;
use App\Catrobat\Events\ProgramAfterInsertEvent;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\AsyncHttpClient;
use App\Catrobat\Services\RemixData;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;


/**
 * Class RemixUpdater
 * @package App\Catrobat\Listeners
 */
class RemixUpdater
{
  const MIGRATION_LOCK_FILE_NAME = 'CatrobatRemixMigration.lock';
  /**
   * @var RemixManager The remix manager.
   */
  private $remix_manager;

  /**
   * @var AsyncHttpClient
   */
  private $async_http_client;

  /**
   * @var RouterInterface The router.
   */
  private $router;

  /**
   * @var string
   */
  private $migration_lock_file_path;

  /**
   * RemixUpdater constructor.
   *
   * @param RemixManager $remix_manager
   * @param AsyncHttpClient $async_http_client
   * @param RouterInterface $router
   * @param $kernel_root_dir
   */
  public function __construct(RemixManager $remix_manager, AsyncHttpClient $async_http_client, RouterInterface $router,
                              $kernel_root_dir)
  {
    $this->remix_manager = $remix_manager;
    $this->async_http_client = $async_http_client;
    $this->router = $router;
    $app_root_dir = $kernel_root_dir;
    $this->migration_lock_file_path = $app_root_dir . '/' . self::MIGRATION_LOCK_FILE_NAME;
  }

  /**
   * @param ProgramAfterInsertEvent $event
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function onProgramAfterInsert(ProgramAfterInsertEvent $event)
  {
    $this->update($event->getExtractedFile(), $event->getProgramEntity());
  }

  /**
   * @param ExtractedCatrobatFile $file
   * @param Program               $program
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function update(ExtractedCatrobatFile $file, Program $program)
  {
    $remixes_data = $file->getRemixesData($program->getId(), $program->isInitialVersion());
    $scratch_remixes_data = array_filter($remixes_data, function ($remix_data) {
      /**
       * @var $remix_data RemixData
       */
      return $remix_data->isScratchProgram();
    });
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
      $scratch_ids = array_map(function ($data) {
        /**
         * @var $data RemixData
         */
        return $data->getProgramId();
      }, $scratch_remixes_data);
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
