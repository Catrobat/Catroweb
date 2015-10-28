<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramAfterInsertEvent;

class ProgramFbPostListener
{
    private $facebook_post_service;

    public function __construct($facebook_post_service)
    {
        $this->facebook_post_service = $facebook_post_service;
    }

    public function onProgramAfterInsert(ProgramAfterInsertEvent $event)
    {
        if ($event->shouldPostToFacebook()) {
            $this->facebook_post_service->postOnFacebook($event->getProgramEntity());
        }
    }
}
