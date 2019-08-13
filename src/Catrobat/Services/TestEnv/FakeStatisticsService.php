<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\StatisticsService;
use App\Entity\ProgramManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class FakeStatisticsService
 * @package App\Catrobat\Features\Helpers
 */
class FakeStatisticsService extends StatisticsService
{
  /**
   * @var StatisticsService
   */
  private $geocoder_service;
  /**
   * @var
   */
  private $use_real_service;

  /**
   * FakeStatisticsService constructor.
   *
   * @param StatisticsService $geocoder_service
   * @param ProgramManager $program_manager
   * @param EntityManagerInterface $entity_manager
   * @param LoggerInterface $logger
   * @param TokenStorageInterface $security_token_storage
   */
  public function __construct(StatisticsService $geocoder_service, ProgramManager $program_manager,
                              EntityManagerInterface $entity_manager, LoggerInterface $logger,
                              TokenStorageInterface $security_token_storage)
  {
    parent::__construct($program_manager, $entity_manager, $logger, $security_token_storage);
    $this->geocoder_service = $geocoder_service;
  }

  /**
   * @param $event
   * @param $program_id
   * @param $referrer
   * @param $rec_tag_by_program_id
   * @param $rec_by_page_id
   * @param $rec_by_program_id
   * @param $locale
   * @param bool $not_needed
   *
   * @return bool
   * @throws \Exception
   */
  public function createProgramDownloadStatistics($event, $program_id, $referrer, $rec_tag_by_program_id,
                                                  $rec_by_page_id, $rec_by_program_id, $locale, $not_needed = false)
  {
    if ($this->use_real_service)
    {
      return $this->geocoder_service->createProgramDownloadStatistics($event, $program_id, $referrer,
        $rec_tag_by_program_id, $rec_by_page_id, $rec_by_program_id, $locale);
    }

    return true;
  }

  /**
   * @param $request
   * @param $type
   * @param $rec_from_id
   * @param $rec_program_id
   * @param $tag_id
   * @param $extension_name
   * @param $referrer
   * @param bool $not_needed
   * @param bool $not_needed2
   * @param bool $not_needed3
   * @param bool $not_needed4
   *
   * @return bool
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function createClickStatistics($request, $type, $rec_from_id, $rec_program_id, $tag_id, $extension_name,
                                        $referrer, $not_needed = false, $not_needed2 = false, $not_needed3 = false,
                                        $not_needed4 = false)
  {
    return $this->geocoder_service->createClickStatistics($request, $type, $rec_from_id, $rec_program_id,
      $tag_id, $extension_name, $referrer);
  }

  /**
   * @param $request
   * @param $type
   * @param $program_id
   * @param $referrer
   * @param $locale
   *
   * @return bool
   * @throws \Exception
   */
  public function createHomepageProgramClickStatistics($request, $type, $program_id, $referrer, $locale)
  {
    return $this->geocoder_service->createHomepageProgramClickStatistics($request, $type, $program_id, $referrer, $locale);
  }

  /**
   * @param $use_real
   */
  public function useRealService($use_real)
  {
    $this->use_real_service = $use_real;
  }
}
