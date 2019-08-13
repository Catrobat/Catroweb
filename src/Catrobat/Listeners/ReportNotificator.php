<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ReportInsertEvent;
use App\Entity\Notification;
use App\Repository\NotificationRepository;

/**
 * Class ReportNotificator
 * @package App\Catrobat\Listeners
 */
class ReportNotificator
{
  /**
   * @var \Swift_Mailer
   */
  private $mailer;
  /**
   * @var NotificationRepository
   */
  private $notification_repo;

  /**
   * ReportNotificator constructor.
   *
   * @param \Swift_Mailer                                     $mailer
   * @param NotificationRepository $repository
   */
  public function __construct(\Swift_Mailer $mailer, NotificationRepository $repository)
  {
    $this->mailer = $mailer;
    $this->notification_repo = $repository;
  }

  /**
   * @param ReportInsertEvent $event
   */
  public function onReportInsertEvent(ReportInsertEvent $event)
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

      $message = (new \Swift_Message())
        ->setSubject('[Pocketcode] reported project!')
        ->setFrom('noreply@catrob.at')
        ->setTo($user->getUser()->getEmail())
        ->setContentType('text/html')
        ->setBody('A Project got reported!

Note: ' . $event->getNote() . '
Project Name:' . $program->getName() . '
Project Description: ' . $program->getDescription() . '

');

      $this->mailer->send($message);
    }
  }
}
