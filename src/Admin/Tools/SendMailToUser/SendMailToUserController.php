<?php

declare(strict_types=1);

namespace App\Admin\Tools\SendMailToUser;

use App\DB\Entity\User\User;
use App\System\Mail\MailerAdapter;
use App\User\UserManager;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * @phpstan-extends CRUDController<\stdClass>
 */
class SendMailToUserController extends CRUDController
{
  public function __construct(
    protected MailerAdapter $mailer,
    protected UserManager $user_manager,
    protected LoggerInterface $logger,
    private readonly ResetPasswordHelperInterface $resetPasswordHelper,
    protected VerifyEmailHelperInterface $verify_email_helper
  ) {
  }

  public function listAction(Request $request): Response
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

    return match ($template) {
      'confirmation' => $this->renderConfirmation($request),
      'reset' => $this->renderReset($request),
      'basic' => $this->renderBasic($request),
      default => new Response('Not Found', Response::HTTP_NOT_FOUND),
    };
  }

  public function renderConfirmation(Request $request): Response
  {
    $user = $this->user_manager->findUserByUsername((string) $request->query->get('username'));
    if (!$user) {
      return new Response('User does not exist', Response::HTTP_NOT_FOUND);
    }

    $signature = $this->verify_email_helper->generateSignature(
      'registration_confirmation_route',
      'user_id',
      'user@email.com'
    );

    $expirationTime = $signature->getExpiresAt();

    $confirm = 'https:://example.url'; // TODO: CHANGE!

    return $this->render('security/registration/new_confirmation_email.html.twig', [
      'signedUrl' => $confirm,
      'deleteUrl' => $confirm,
      'user' => $user,
      'expire' => $expirationTime->format('H:i'),
    ]);
  }

  public function renderReset(Request $request): Response
  {
    $user = $this->user_manager->findUserByUsername((string) $request->query->get('username'));
    if (!$user) {
      return new Response('User does not exist', Response::HTTP_NOT_FOUND);
    }

    $resetToken = $this->resetPasswordHelper->generateFakeResetToken();

    $signature = $this->verify_email_helper->generateSignature(
      'registration_confirmation_route',
      'user_id',
      'user@email.com'
    );

    $expirationTime = $signature->getExpiresAt();

    $confirm = 'this is the url'; // TODO: CHANGE!

    return $this->render('security/reset_password/new_email.html.twig', [
      'resetToken' => $resetToken,
      'user' => $user,
      'signedUrl' => $confirm,
      'deleteUrl' => $confirm,
      'expire' => $expirationTime->format('H:i'),
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

    $title = (string) $request->query->get('title');

    $messageText = (string) $request->query->get('message');
    if ('' === $messageText) {
      return new Response('Empty message!', Response::HTTP_BAD_REQUEST);
    }

    // $htmlText = str_replace(PHP_EOL, '<br>', $messageText);
    $text = str_replace(PHP_EOL, ' ', $messageText);
    $htmlText = wordwrap($text, 60, "<br>\n");

    return $this->render('Admin/Tools/Email/new_simple_message.html.twig', [
      'message' => $htmlText,
      'subject' => $subject,
      'title' => $title,
    ]);
  }
}
