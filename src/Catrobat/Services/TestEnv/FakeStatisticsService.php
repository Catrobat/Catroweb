<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\StatisticsService;

/**
 * Class FakeStatisticsService
 * @package App\Catrobat\Features\Helpers
 */
class FakeStatisticsService
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
   */
  public function __construct(StatisticsService $geocoder_service)
  {
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
   *
   * @return bool
   * @throws \Geocoder\Exception\Exception
   */
  public function createProgramDownloadStatistics($event, $program_id, $referrer, $rec_tag_by_program_id, $rec_by_page_id, $rec_by_program_id, $locale)
  {
    if ($this->use_real_service)
    {
      return $this->geocoder_service->createProgramDownloadStatistics($event, $program_id, $referrer, $rec_tag_by_program_id, $rec_by_page_id, $rec_by_program_id, $locale);
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
   *
   * @return bool
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   * @throws \Geocoder\Exception\Exception
   */
  public function createClickStatistics($request, $type, $rec_from_id, $rec_program_id, $tag_id, $extension_name, $referrer)
  {
    return $this->geocoder_service->createClickStatistics($request, $type, $rec_from_id, $rec_program_id, $tag_id, $extension_name, $referrer);
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
