<?php
namespace Catrobat\AppBundle\Features\Helpers;

use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\DependencyInjection\Container;

/**
 * @Route(service="download.statistics")
 */
class FakeDownloadStatisticsService
{
    public function __construct()
    {
    }

    public function createProgramDownloadStatistics(Program $program, $ip)
    {
        echo 'FAKE';
        return true;
    }
}
