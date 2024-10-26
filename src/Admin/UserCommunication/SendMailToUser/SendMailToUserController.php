<?php

declare(strict_types=1);

namespace App\Admin\UserCommunication\SendMailToUser;

use App\DB\Entity\User\User;
use App\Security\Authentication\ResetPasswordEmail;
use App\Security\Authentication\VerifyEmail;
use App\System\Mail\MailerAdapter;
use App\User\UserManager;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
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
    protected VerifyEmailHelperInterface $verify_email_helper,
    protected ParameterBagInterface $parameter_bag,
    protected VerifyEmail $verify_email,
    protected ResetPasswordEmail $reset_password_email,
  ) {
  }

  #[\Override]
  public function listAction(Request $request): Response
  {
    return $this->render('Admin/UserCommunication/SendMail.html.twig');
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

    $title = (string) $request->query->get('title');
    if ('' === $title) {
      return new Response('Empty title!', Response::HTTP_BAD_REQUEST);
    }

    $htmlText = str_replace(PHP_EOL, '<br>', $messageText);
    $mailTo = $user->getEmail();
    $this->mailer->send(
      $mailTo,
      $subject,
      'Admin/UserCommunication/SendMail/SimpleMessage.html.twig',
      [
        'subject' => $subject,
        'title' => $title,
        'message' => $htmlText,
      ]
    );

    return new Response('OK - message sent', Response::HTTP_OK);
  }

  public function previewAction(Request $request): Response
  {
    $template = (string) $request->query->get('template');

    return match ($template) {
      'confirmation' => $this->renderVerifyEmail($request),
      'reset' => $this->renderResetPasswordEmail($request),
      'basic' => $this->renderBasicEmail($request),
      default => new Response('Not Found', Response::HTTP_NOT_FOUND),
    };
  }

  public function renderVerifyEmail(Request $request): Response
  {
    $user = $this->user_manager->findUserByUsername((string) $request->query->get('username'));
    if (!$user instanceof UserInterface) {
      return new Response('User does not exist', Response::HTTP_NOT_FOUND);
    }

    $this->verify_email->init($user);

    return $this->render($this->verify_email->getTemplate(), $this->verify_email->getContext());
  }

  public function renderResetPasswordEmail(Request $request): Response
  {
    $user = $this->user_manager->findUserByUsername((string) $request->query->get('username'));
    if (!$user instanceof UserInterface) {
      return new Response('User does not exist', Response::HTTP_NOT_FOUND);
    }

    $this->reset_password_email->init($user, '');

    return $this->render($this->reset_password_email->getTemplate(), $this->reset_password_email->getFakeContext());
  }

  public function renderBasicEmail(Request $request): Response
  {
    $user = $this->user_manager->findUserByUsername((string) $request->query->get('username'));
    if (!$user instanceof UserInterface) {
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

    return $this->render('Admin/UserCommunication/SendMail/SimpleMessage.html.twig', [
      'message' => $htmlText,
      'subject' => $subject,
      'title' => $title,
    ]);
  }
}
