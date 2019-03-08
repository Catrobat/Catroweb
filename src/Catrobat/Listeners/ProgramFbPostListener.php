<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Services\FacebookPostService;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;


/**
 * Class ProgramFbPostListener
 * @package App\Catrobat\Listeners
 */
class ProgramFbPostListener
{
  /**
   * @var FacebookPostService
   */
  private $facebook_post_service;

  /**
   * ProgramFbPostListener constructor.
   *
   * @param $facebook_post_service
   */
  public function __construct($facebook_post_service)
  {
    $this->facebook_post_service = $facebook_post_service;
  }

  /**
   * @param PostResponseEvent $event
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   * @throws \Facebook\Exceptions\FacebookSDKException
   */
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
