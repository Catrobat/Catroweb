<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramAfterInsertEvent;

class UploadNotificator
{
    private $mailer;
    private $notification_repo;

    public function __construct(\Swift_Mailer $mailer,  \Catrobat\AppBundle\Entity\NotificationRepository $repository)
    {
        $this->mailer = $mailer;
        $this->notification_repo = $repository;
    }

    public function onProgramInsertEvent(ProgramAfterInsertEvent $event)
    {

    /* @var $notification_repo \Catrobat\AppBundle\Entity\NotificationRepository */
    $notification_repo = $this->notification_repo;
        $all_users = $notification_repo->findAll();
        foreach ($all_users as $user) {
            /* @var $user \Catrobat\AppBundle\Entity\Notification */
      if (!$user->getUpload()) {
          continue;
      }
            $program = $event->getProgramEntity();

            $message = (new \Swift_Message())
      ->setSubject('[Pocketcode] Project upload')
      ->setFrom('noreply@catrob.at')
      ->setTo($user->getUser()->getEmail())
      ->setContentType('text/html')
      ->setBody('A Project was uploaded.

Name: '.$program->getName().'
Description: '.$program->getDescription().'
')
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
