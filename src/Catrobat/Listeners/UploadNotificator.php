<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramAfterInsertEvent;

/**
 * Class UploadNotificator
 * @package App\Catrobat\Listeners
 */
class UploadNotificator
{
  /**
   * @var \Swift_Mailer
   */
  private $mailer;
  /**
   * @var \App\Repository\NotificationRepository
   */
  private $notification_repo;

  /**
   * UploadNotificator constructor.
   *
   * @param \Swift_Mailer                                     $mailer
   * @param \App\Repository\NotificationRepository $repository
   */
  public function __construct(\Swift_Mailer $mailer, \App\Repository\NotificationRepository $repository)
  {
    $this->mailer = $mailer;
    $this->notification_repo = $repository;
  }

  /**
   * @param ProgramAfterInsertEvent $event
   */
  public function onProgramInsertEvent(ProgramAfterInsertEvent $event)
  {

    /* @var $notification_repo \App\Repository\NotificationRepository */
    $notification_repo = $this->notification_repo;
    $all_users = $notification_repo->findAll();
    foreach ($all_users as $user)
    {
      /* @var $user \App\Entity\Notification */
      if (!$user->getUpload())
      {
        continue;
      }
      $program = $event->getProgramEntity();

      $message = (new \Swift_Message())
        ->setSubject('[Pocketcode] Project upload')
        ->setFrom('noreply@catrob.at')
        ->setTo($user->getUser()->getEmail())
        ->setContentType('text/html')
        ->setBody('A Project was uploaded.

Name: ' . $program->getName() . '
Description: ' . $program->getDescription() . '
')/*
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
