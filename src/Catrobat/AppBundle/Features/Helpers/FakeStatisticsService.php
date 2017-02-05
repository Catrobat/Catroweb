<?php
namespace Catrobat\AppBundle\Features\Helpers;

use Catrobat\AppBundle\Services\StatisticsService;

class FakeStatisticsService
{
    private $geocoder_service;
    private $use_real_service;

    public function __construct(StatisticsService $geocoder_service)
    {
        $this->geocoder_service = $geocoder_service;
    }

    public function createProgramDownloadStatistics($event, $program_id, $referrer, $rec_tag_by_program_id, $rec_by_page_id, $rec_by_program_id)
    {
        if ($this->use_real_service) {
            return $this->geocoder_service->createProgramDownloadStatistics($event, $program_id, $referrer, $rec_tag_by_program_id, $rec_by_page_id, $rec_by_program_id);
        }
        return true;
    }

    public function createClickStatistics($request, $type, $rec_from_id, $rec_program_id, $tag_id, $extension_name, $referrer)
    {
        return $this->geocoder_service->createClickStatistics($request, $type, $rec_from_id, $rec_program_id, $tag_id, $extension_name, $referrer);
    }

    public function useRealService($use_real) {
        $this->use_real_service = $use_real;
    }
}
