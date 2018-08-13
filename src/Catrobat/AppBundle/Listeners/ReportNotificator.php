<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ReportInsertEvent;

class ReportNotificator
{
    private $mailer;
    private $notification_repo;

    public function __construct(\Swift_Mailer $mailer,  \Catrobat\AppBundle\Entity\NotificationRepository $repository)
    {
        $this->mailer = $mailer;
        $this->notification_repo = $repository;
    }

    public function onReportInsertEvent(ReportInsertEvent $event)
    {
    /* @var $notification_repo \Catrobat\AppBundle\Entity\NotificationRepository */

        $notification_repo = $this->notification_repo;
        $all_users = $notification_repo->findAll();
        $notification = $event->getReport();
        $program = $notification->getProgram();

        foreach ($all_users as $user) {
            /* @var $user \Catrobat\AppBundle\Entity\Notification */
      if (!$user->getReport()) {
          continue;
      }

            $message = (new \Swift_Message())
      ->setSubject('[Pocketcode] reported project!')
      ->setFrom('noreply@catrob.at')
      ->setTo($user->getUser()->getEmail())
      ->setContentType('text/html')
       ->setBody('A Project got reported!

Note: '.$event->getNote().'
Project Name:'.$program->getName().'
Project Description: '.$program->getDescription().'

');

            $this->mailer->send($message);
        }
    }
}
