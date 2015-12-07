<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramDownloadedEvent;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class DownloadStatisticsListener
{
    private $download_statistics_service;

    public function __construct($download_statistics_service)
    {
        $this->download_statistics_service = $download_statistics_service;
    }
    
    public function onTerminateEvent(PostResponseEvent $event)
    {
        $attributes = $event->getRequest()->attributes;
        if ($attributes->has('download_statistics_program_id')) {
            $program_id = $attributes->get('download_statistics_program_id');
            $ip = $event->getRequest()->server->get('REMOTE_ADDR');
            $user_agent = $event->getRequest()->headers->get('User-Agent') ;
            $this->createProgramDownloadStatistics($program_id, $ip, $user_agent);
            $event->getRequest()->attributes->remove('download_statistics_program_id');
        }

    }

    public function createProgramDownloadStatistics($program_id, $ip, $user_agent)
    {
        if (strpos($user_agent, 'okhttp') === false) {
            $this->download_statistics_service->createProgramDownloadStatistics($program_id, $ip, $user_agent);
        }
    }
}
