<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramAfterInsertEvent;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Swift_Mailer;
use Swift_Message;

/**
 * Class UploadNotificator.
 */
class UploadNotificator
{
  private Swift_Mailer $mailer;

  private NotificationRepository $notification_repo;

  /**
   * UploadNotificator constructor.
   */
  public function __construct(Swift_Mailer $mailer, NotificationRepository $repository)
  {
    $this->mailer = $mailer;
    $this->notification_repo = $repository;
  }

  public function onProgramInsertEvent(ProgramAfterInsertEvent $event)
  {
    /* @var $notification_repo NotificationRepository */
    $notification_repo = $this->notification_repo;
    $all_notifications = $notification_repo->findAll();
    foreach ($all_notifications as $notification)
    {
      /* @var $notification Notification */
      if (!$notification->getUpload())
      {
        continue;
      }
      $program = $event->getProgramEntity();

      $body = 'A Project was uploaded.\n\nName: '.$program->getName().'Description: '.$program->getDescription();

      $message = (new Swift_Message())
        ->setSubject('[Pocketcode] Project upload')
        ->setFrom('noreply@catrob.at')
        ->setTo($notification->getUser()->getEmail())
        ->setContentType('text/html')
        ->setBody($body)
      ;
      /*
         * If you also want to include a plaintext version of the message
       *
        ->addPart(
            $this->renderView(
                'Emails/registration.txt.twig',
                array('name' => $name)
            ),
            'text/plain'
        )
        */

      $this->mailer->send($message);
    }
  }
}
