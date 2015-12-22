<?php
namespace Catrobat\AppBundle\Features\Helpers;

use Catrobat\AppBundle\Services\DownloadStatisticsService;

class FakeDownloadStatisticsService
{
    private $geocoder_service;
    private $use_real_service;

    public function __construct(DownloadStatisticsService $geocoder_service)
    {
        $this->geocoder_service = $geocoder_service;
    }

    public function createProgramDownloadStatistics($program_id, $ip, $user_agent, $user_name, $referrer)
    {
        if ($this->use_real_service) {
            return $this->geocoder_service->createProgramDownloadStatistics($program_id, $ip, $user_agent, $user_name, $referrer);
        }
        return true;
    }

    public function useRealService($use_real) {
        $this->use_real_service = $use_real;
    }
}
