<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ReportInsertEvent;

use Catrobat\AppBundle\StatusCode;

class FacebookRemovePostListener
{
    private $facebook_post_service;
    private $entity_manager;

    public function __construct($facebook_post_service, $entity_manager)
    {
        $this->facebook_post_service = $facebook_post_service;
        $this->entity_manager = $entity_manager;
    }

    public function onReportInsertEvent(ReportInsertEvent $event)
    {
        $notification = $event->getReport();
        $program = $notification->getProgram();

        $status_code = $this->facebook_post_service->removeFbPost($program);
        if ($status_code != StatusCode::FB_DELETE_ERROR) {
            $program->setFbPostId('');
            $this->entity_manager->persist($program);
            $this->entity_manager->flush();
        }
    }
}
