<?php

namespace Catrobat\AppBundle\Listeners\Entity;


use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Services\FacebookPostService;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ProgramListener
{
    private $facebook_post_service;

    public function __construct(FacebookPostService $facebook_post_service)
    {
        $this->facebook_post_service = $facebook_post_service;
    }

    public function preUpdate(Program $program, LifecycleEventArgs $eventArgs)
    {
        $fb_post_id = $program->getFbPostId();

        if ($eventArgs->hasChangedField('visible') && !$eventArgs->getNewValue('visible') &&
            $fb_post_id != null && $fb_post_id != '') {
            $status_code = $this->facebook_post_service->removeFbPost($fb_post_id);

            if ($status_code != StatusCode::FB_DELETE_ERROR) {
                $eventArgs->setNewValue('fb_post_id', '');
                $eventArgs->setNewValue('fb_post_url', '');
            }
        }
    }
}