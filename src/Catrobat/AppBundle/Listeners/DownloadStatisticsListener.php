<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramDownloadedEvent;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\RecommenderSystem\RecommendedPageId;
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
            $locale = strtolower($request->getLocale());

            $rec_by_page_id = null;
            $rec_by_program_id = 0;

            $rec_tag_by_program_id = null;

            if ($attributes->has('rec_by_page_id') && RecommendedPageId::isValidRecommendedPageId($attributes->get('rec_by_page_id'))) {
                // all recommendations (except tag-recommendations -> see below)
                $rec_by_page_id = $attributes->get('rec_by_page_id');
                if ($attributes->has('rec_by_program_id')) {
                    $rec_by_program_id = intval($request->query->get('rec_by_program_id', 0));
                }
            } else if ($attributes->has('rec_from')) {
                // tag-recommendations
                $rec_tag_by_program_id = $attributes->get('rec_from');
            }

            $this->createProgramDownloadStatistics($request, $program_id, $referrer, $rec_tag_by_program_id, $rec_by_page_id, $rec_by_program_id, $locale);
            $event->getRequest()->attributes->remove('download_statistics_program_id');
        }
    }

    public function createProgramDownloadStatistics($request, $program_id, $referrer, $rec_tag_by_program_id, $rec_by_page_id, $rec_by_program_id, $locale)
    {
        if ((strpos($request->headers->get('User-Agent'), 'okhttp') === false) || ($rec_by_page_id != null)) {
            $this->statistics_service->createProgramDownloadStatistics($request, $program_id, $referrer, $rec_tag_by_program_id, $rec_by_page_id, $rec_by_program_id, $locale);
        }
    }
}
