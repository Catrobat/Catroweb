<?php

namespace App\Admin\Tools\SendMailToUser;

use App\DB\Entity\User\User;
use App\System\Mail\MailerAdapter;
use App\User\UserManager;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * @phpstan-extends CRUDController<\stdClass>
 */
class SendMailToUserController extends CRUDController
{
  public function __construct(
    protected MailerAdapter $mailer,
    protected UserManager $user_manager,
    protected LoggerInterface $logger,
    private readonly ResetPasswordHelperInterface $resetPasswordHelper
  ) {
  }

  public function listAction(Request $request = null): Response
  {
    return $this->renderWithExtraParams('Admin/Tools/send_mail_to_user.html.twig');
  }

  public function sendAction(Request $request): Response
  {
    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername((string) $request->query->get('username'));
    if (!$user) {
      return new Response('User does not exist', Response::HTTP_BAD_REQUEST);
    }
    $subject = (string) $request->query->get('subject');
    if ('' === $subject) {
      return new Response('Empty subject!', Response::HTTP_BAD_REQUEST);
    }

    $messageText = (string) $request->query->get('message');
    if ('' === $messageText) {
      return new Response('Empty message!', Response::HTTP_BAD_REQUEST);
    }
    $htmlText = str_replace(PHP_EOL, '<br>', $messageText);
    $mailTo = $user->getEmail();
    $this->mailer->send(
      $mailTo,
      $subject,
      'Admin/Tools/Email/simple_message.html.twig',
      ['message' => $htmlText]
    );

    return new Response('OK - message sent', Response::HTTP_OK);
  }

  public function previewAction(Request $request): Response
  {
    $template = (string) $request->query->get('template');

    switch ($template) {
      case 'confirmation':
        return $this->renderConfirmation($request);
      case 'reset':
        return $this->renderReset($request);
      case 'basic':
        return $this->renderBasic($request);
    }

    return new Response('Not Found', Response::HTTP_NOT_FOUND);
  }

  public function renderConfirmation(Request $request): Response
  {
    $signature = 'https:://example.url';
    $user = $this->user_manager->findUserByUsername((string) $request->query->get('username'));
    if (!$user) {
      $user['username'] = 'User';
    }

    return $this->render('security/registration/confirmation_email.html.twig', [
      'signedUrl' => $signature,
      'user' => $user,
    ]);
  }

  public function renderReset(Request $request): Response
  {
    $resetToken = $this->resetPasswordHelper->generateFakeResetToken();

    return $this->render('security/reset_password/email.html.twig', [
      'resetToken' => $resetToken,
    ]);
  }

  public function renderBasic(Request $request): Response
  {
    $user = $this->user_manager->findUserByUsername((string) $request->query->get('username'));
    if (!$user) {
      return new Response('User does not exist', Response::HTTP_NOT_FOUND);
    }

    $subject = (string) $request->query->get('subject');
    if ('' === $subject) {
      return new Response('Empty subject!', Response::HTTP_BAD_REQUEST);
    }

    $messageText = (string) $request->query->get('message');
    if ('' === $messageText) {
      return new Response('Empty message!', Response::HTTP_BAD_REQUEST);
    }

    $htmlText = str_replace(PHP_EOL, '<br>', $messageText);

    return $this->render('Admin/Tools/Email/simple_message.html.twig', [
      'message' => $htmlText,
    ]);
  }
}
