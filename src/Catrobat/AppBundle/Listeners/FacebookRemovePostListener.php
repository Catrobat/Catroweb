<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\StatusCode;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class FacebookRemovePostListener
{
    private $facebook_post_service;
    private $entity_manager;
    private $program_manager;

    public function __construct($facebook_post_service, $entity_manager, $program_manager)
    {
        $this->facebook_post_service = $facebook_post_service;
        $this->entity_manager = $entity_manager;
        $this->program_manager = $program_manager;
    }

    public function onTerminateEvent(PostResponseEvent $event)
    {
        $attributes = $event->getRequest()->attributes;
        if ($attributes->has('remove_post_from_facebook') && $attributes->has('program_id')) {
            $program_id = $attributes->get('program_id');
            $status_code = $this->facebook_post_service->removeFbPost($program_id);
            if ($status_code != StatusCode::FB_DELETE_ERROR) {
                $program = $this->program_manager->find($program_id);
                $program->setFbPostId('');
                $this->entity_manager->persist($program);
                $this->entity_manager->flush();
            }
        }
    }
}
