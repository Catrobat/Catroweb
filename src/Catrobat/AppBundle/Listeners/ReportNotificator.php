<?php
namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Entity\NotificationRepository;
use Catrobat\AppBundle\Entity\Notification;
use Catrobat\AppBundle\Events\ReportInsertEvent;
use Catrobat\AppBundle\Model\UserManager;
use Catrobat\AppBundle\Entity\User;

class ReportNotificator
{
  private $mailer;
  private $notification_repo;

  function __construct(\Swift_Mailer $mailer,  \Catrobat\AppBundle\Entity\NotificationRepository $repository)
  {
    $this->mailer = $mailer;
    $this->notification_repo = $repository;
  }

  function onReportInsertEvent(ReportInsertEvent $event)
  {

    /* @var $notification_repo \Catrobat\AppBundle\Entity\NotificationRepository */
    $notification_repo = $this->notification_repo;
    $all_users = $notification_repo->findAll();
    $notification = $event->getReport();
    $program = $notification->getProject();
    echo "A Project got reported!

Note: ".$event->getNote()."
Project Name:".$program->getName()."
Project Description: ".$program->getDescription()."

";
    foreach($all_users as $user)
    {
      /* @var $user \Catrobat\AppBundle\Entity\Notification */
      if(!$user->getReport())
      {
        continue;
      }
      $message = $this->mailer->createMessage()
          ->setSubject('[Pocketcode] reported project!')
          ->setTo($user->getUser()->getEmail())
          ->setBody("A Project got reported!

Note: ".$event->getNote()."
Project Name:".$program->getName()."
Project Description: ".$program->getDescription()."

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
