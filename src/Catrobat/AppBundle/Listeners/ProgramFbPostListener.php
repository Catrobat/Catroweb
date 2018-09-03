<?php

namespace Catrobat\AppBundle\Listeners;

use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class ProgramFbPostListener
{
  private $facebook_post_service;

  public function __construct($facebook_post_service)
  {
    $this->facebook_post_service = $facebook_post_service;
  }

  public function onTerminateEvent(PostResponseEvent $event)
  {
    $attributes = $event->getRequest()->attributes;
    if ($attributes->has('post_to_facebook') && $attributes->has('program_id'))
    {
      $program_id = $attributes->get('program_id');
      $this->facebook_post_service->postOnFacebook($program_id);
    }
  }
}
