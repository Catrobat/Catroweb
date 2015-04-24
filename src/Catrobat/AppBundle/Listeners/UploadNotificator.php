<?php
namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Entity\NotificationRepository;
use Catrobat\AppBundle\Entity\Notification;
use Catrobat\AppBundle\Events\ProgramAfterInsertEvent;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Entity\User;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class UploadNotificator
{
  private $logger;
  private $mailer;
  private $notification_repo;

  function __construct(Logger $logger, \Swift_Mailer $mailer,  \Catrobat\AppBundle\Entity\NotificationRepository $repository)
  {
    $this->logger = $logger;
    $this->mailer = $mailer;
    $this->notification_repo = $repository;
  }

  function onProgramInsertEvent(ProgramAfterInsertEvent $event)
  {

    /* @var $notification_repo \Catrobat\AppBundle\Entity\NotificationRepository */
    $notification_repo = $this->notification_repo;
    $all_users = $notification_repo->findAll();
    foreach($all_users as $user)
    {
      /* @var $user \Catrobat\AppBundle\Entity\Notification */
      if(!$user->getUpload())
      {
        continue;
      }
      $program = $event->getProgramEntity();
      $message = $this->mailer->createMessage()
          ->setSubject('[Pocketcode] Project upload')
          ->setTo($user->getUser()->getEmail())
          ->setBody("A Project was uploaded.

Name: ".$program->getName()."
Description: ".$program->getDescription()."
")
        /*
         * If you also want to include a plaintext version of the message
        ->addPart(
            $this->renderView(
                'Emails/registration.txt.twig',
                array('name' => $name)
            ),
            'text/plain'
        )
        */
      ;
      $this->mailer->send($message);
    }
  }
}
