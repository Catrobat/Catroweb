<?php

namespace App\Admin\Tools\SendMailToUser;

use App\DB\Entity\User\User;
use App\User\UserManager;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class SendMailToUserController extends CRUDController
{
  protected MailerInterface $mailer;
  protected UserManager $user_manager;
  protected LoggerInterface $logger;

  public function __construct(MailerInterface $mailer, UserManager $user_manager, LoggerInterface $logger)
  {
    $this->user_manager = $user_manager;
    $this->mailer = $mailer;
    $this->logger = $logger;
  }

  public function listAction(Request $request = null): Response
  {
    return $this->renderWithExtraParams('Admin/Tools/send_mail_to_user.html.twig');
  }

  public function sendAction(Request $request): Response
  {
    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($request->get('username'));
    if (!$user) {
      return new Response('User does not exist');
    }
    $subject = $request->get('subject');
    if (null === $subject || '' === $subject) {
      return new Response('Empty subject!');
    }

    $messageText = $request->get('message');
    if (null === $messageText || '' === $messageText) {
      return new Response('Empty message!');
    }
    $htmlText = str_replace(PHP_EOL, '<br>', $messageText);

    $mailTo = $user->getEmail();
    try {
      $email = (new TemplatedEmail())
        ->from(new Address('share@catrob.at'))
        ->to($mailTo)
        ->subject($subject)
        ->htmlTemplate('Admin/Tools/Email/simple_message.html.twig')
        ->context([
          ['message' => $htmlText],
        ])
      ;
      $this->mailer->send($email);
    } catch (TransportExceptionInterface $e) {
      $this->logger->error("Can't send email to {$mailTo}; Reason ".$e->getMessage());
    }

    return new Response('OK - message sent');
  }
}
