<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ReportInsertEvent;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Swift_Mailer;
use Swift_Message;

class ReportNotificator
{
  private Swift_Mailer $mailer;
  private NotificationRepository $notification_repo;

  public function __construct(Swift_Mailer $mailer, NotificationRepository $repository)
  {
    $this->mailer = $mailer;
    $this->notification_repo = $repository;
  }

  public function onReportInsertEvent(ReportInsertEvent $event): void
  {
    /* @var $notification_repo NotificationRepository */

    $notification_repo = $this->notification_repo;
    $all_users = $notification_repo->findAll();
    $notification = $event->getReport();
    $program = $notification->getProgram();

    foreach ($all_users as $user)
    {
      /* @var $user Notification */
      if (!$user->getReport())
      {
        continue;
      }

      $message = (new Swift_Message())
        ->setSubject('[Pocketcode] reported project!')
        ->setFrom('noreply@catrob.at')
        ->setTo($user->getUser()->getEmail())
        ->setContentType('text/html')
        ->setBody('A Project got reported!

Note: '.$event->getNote().'
Project Name:'.$program->getName().'
Project Description: '.$program->getDescription().'

')
      ;

      $this->mailer->send($message);
    }
  }
}
