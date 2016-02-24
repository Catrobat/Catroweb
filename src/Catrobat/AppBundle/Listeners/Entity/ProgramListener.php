<?php

namespace Catrobat\AppBundle\Listeners\Entity;


use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Services\FacebookPostService;
use Catrobat\AppBundle\StatusCode;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class ProgramListener
{
    private $facebook_post_service;

    public function __construct($facebook_post_service)
    {
        $this->facebook_post_service = $facebook_post_service;
    }

    public function preUpdate(Program $program, PreUpdateEventArgs $eventArgs)
    {
        $fb_post_id = $program->getFbPostId();

        if ($eventArgs->hasChangedField('visible') && !$eventArgs->getNewValue('visible') &&
            $fb_post_id != null && $fb_post_id != '') {
            $status_code = $this->facebook_post_service->removeFbPost($fb_post_id);

            if ($status_code != StatusCode::FB_DELETE_ERROR) {
                $eventArgs->getEntity()->setFbPostId('');
                $eventArgs->getEntity()->setFbPostUrl('');
                $eventArgs->getEntityManager()->getUnitOfWork()->recomputeSingleEntityChangeSet($eventArgs->getEntityManager()->getClassMetadata('Catrobat\AppBundle\Entity\Program'), $eventArgs->getEntity());

            }
        }
    }
}