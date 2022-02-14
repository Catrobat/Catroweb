<?php

namespace App\Admin\Tools\SendMailToUser;

use App\DB\Entity\User\User;
use App\System\Mail\MailerAdapter;
use App\User\UserManager;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SendMailToUserController extends CRUDController
{
  protected MailerAdapter $mailer;
  protected UserManager $user_manager;
  protected LoggerInterface $logger;

  public function __construct(MailerAdapter $mailer, UserManager $user_manager, LoggerInterface $logger)
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
    $this->mailer->send(
      $mailTo,
      $subject,
      'Admin/Tools/Email/simple_message.html.twig',
      ['message' => $htmlText]
    );

    return new Response('OK - message sent');
  }
}
