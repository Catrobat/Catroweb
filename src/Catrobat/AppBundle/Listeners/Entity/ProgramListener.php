<?php

namespace Catrobat\AppBundle\Listeners\Entity;


use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Services\FacebookPostService;
use Catrobat\AppBundle\StatusCode;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bridge\Monolog\Logger;

class ProgramListener
{
  private $facebook_post_service;
  private $logger;

  public function __construct($facebook_post_service, Logger $logger)
  {
    $this->facebook_post_service = $facebook_post_service;
    $this->logger = $logger;
  }

  public function preUpdate(Program $program, PreUpdateEventArgs $eventArgs)
  {
    $fb_post_id = $program->getFbPostId();

    if ($eventArgs->hasChangedField('visible') && !$eventArgs->getNewValue('visible') &&
      $fb_post_id != null && $fb_post_id != '')
    {

      try
      {
        $status_code = $this->facebook_post_service->removeFbPost($fb_post_id);

        if ($status_code != StatusCode::FB_DELETE_ERROR)
        {
          $eventArgs->getEntity()->setFbPostId('');
          $eventArgs->getEntity()->setFbPostUrl('');
          $eventArgs->getEntityManager()->getUnitOfWork()->recomputeSingleEntityChangeSet($eventArgs->getEntityManager()->getClassMetadata('Catrobat\AppBundle\Entity\Program'), $eventArgs->getEntity());
        }
      } catch (Exception $e)
      {
        $this->logger->error('ProgramListener->preUpdate: FbPostId[' . $fb_post_id . '], Message[' . $e->getMessage() . ']');
      }
    }
  }
}