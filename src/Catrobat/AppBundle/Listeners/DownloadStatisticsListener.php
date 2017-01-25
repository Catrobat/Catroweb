<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramDownloadedEvent;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class DownloadStatisticsListener
{
    private $statistics_service;
    private $security_token_storage;

    public function __construct($statistics_service, $security_token_storage)
    {
        $this->statistics_service = $statistics_service;
        $this->security_token_storage = $security_token_storage;
    }

    public function onTerminateEvent(PostResponseEvent $event)
    {
        $request = $event->getRequest();
        $attributes = $request->attributes;

        if ($attributes->has('download_statistics_program_id')) {
            $program_id = $attributes->get('download_statistics_program_id');
            $referrer = $attributes->get('referrer');

            if ($attributes->has('rec_from'))
                $rec_id = $attributes->get('rec_from');
            else
                $rec_id = null;

            $this->createProgramDownloadStatistics($request, $program_id, $referrer, $rec_id);
            $event->getRequest()->attributes->remove('download_statistics_program_id');
        }
    }

    public function createProgramDownloadStatistics($request, $program_id, $referrer, $rec_id)
    {
        if (strpos($request->headers->get('User-Agent'), 'okhttp') === false) {
            $this->statistics_service->createProgramDownloadStatistics($request, $program_id, $referrer, $rec_id);
        }
    }
}
