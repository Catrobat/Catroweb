<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramDownloadedEvent;
use Catrobat\AppBundle\Entity\Program;

class DownloadStatisticsListener
{
    private $download_statistics_service;

    public function __construct($download_statistics_service)
    {
        $this->download_statistics_service = $download_statistics_service;
    }
    
    public function onEvent(ProgramDownloadedEvent $event)
    {
        $this->createProgramDownloadStatistics($event->getProgram(), $event->getIp());
    }

    public function createProgramDownloadStatistics(Program $program, $ip)
    {
        $this->download_statistics_service->createProgramDownloadStatistics($program, $ip);
    }
}
