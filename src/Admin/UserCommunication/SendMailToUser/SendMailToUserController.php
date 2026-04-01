<?php

declare(strict_types=1);

namespace App\Admin\UserCommunication\SendMailToUser;

use App\DB\Entity\User\User;
use App\Security\Authentication\ResetPasswordEmail;
use App\Security\Authentication\VerifyEmail;
use App\Security\ParentalConsent\ParentalConsentService;
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
    protected ParentalConsentService $parental_consent_service,
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

    $template = (string) $request->query->get('template', 'basic');

    return match ($template) {
      'confirmation' => $this->sendVerifyEmail($user),
      'consent' => $this->sendConsentEmail($user),
      'management' => $this->sendManagementEmail($user),
      'reset' => $this->sendResetPasswordEmail($user),
      default => $this->sendBasicEmail($user, $request),
    };
  }

  private function sendVerifyEmail(User $user): Response
  {
    $this->verify_email->init($user)->send();

    return new Response('OK - verification email sent to '.$user->getEmail(), Response::HTTP_OK);
  }

  private function sendConsentEmail(User $user): Response
  {
    $parentEmail = $user->getParentEmail();
    if (null === $parentEmail || '' === $parentEmail) {
      return new Response('User has no parent email set', Response::HTTP_BAD_REQUEST);
    }

    $this->parental_consent_service->sendConsentRequest($user);

    return new Response('OK - consent email sent to '.$parentEmail, Response::HTTP_OK);
  }

  private function sendManagementEmail(User $user): Response
  {
    $parentEmail = $user->getParentEmail();
    if (null === $parentEmail || '' === $parentEmail) {
      return new Response('User has no parent email set', Response::HTTP_BAD_REQUEST);
    }

    $this->parental_consent_service->sendManagementLink($parentEmail);

    return new Response('OK - management email sent to '.$parentEmail, Response::HTTP_OK);
  }

  private function sendResetPasswordEmail(User $user): Response
  {
    $this->reset_password_email->init($user, '')->send();

    return new Response('OK - reset password email sent to '.$user->getEmail(), Response::HTTP_OK);
  }

  private function sendBasicEmail(User $user, Request $request): Response
  {
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

    $this->mailer->send(
      $user->getEmail() ?? '',
      $subject,
      'Admin/UserCommunication/SendMail/SimpleMessage.html.twig',
      [
        'subject' => $subject,
        'title' => $title,
        'message' => $messageText,
      ]
    );

    return new Response('OK - message sent to '.$user->getEmail(), Response::HTTP_OK);
  }

  public function previewAction(Request $request): Response
  {
    $template = (string) $request->query->get('template');

    return match ($template) {
      'confirmation' => $this->renderVerifyEmail($request),
      'consent' => $this->renderConsentEmail($request),
      'management' => $this->renderManagementEmail($request),
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

  public function renderConsentEmail(Request $request): Response
  {
    $user = $this->user_manager->findUserByUsername((string) $request->query->get('username'));
    if (!$user instanceof UserInterface) {
      return new Response('User does not exist', Response::HTTP_NOT_FOUND);
    }

    return $this->render('Email/ParentalConsentEmail.html.twig', [
      'signedUrl' => '#preview-link',
      'username' => $user->getUsername(),
      'parentPortalUrl' => '#preview-portal',
      'parentInfoUrl' => '#preview-info',
    ]);
  }

  public function renderManagementEmail(Request $request): Response
  {
    $user = $this->user_manager->findUserByUsername((string) $request->query->get('username'));
    if (!$user instanceof UserInterface) {
      return new Response('User does not exist', Response::HTTP_NOT_FOUND);
    }

    return $this->render('Email/ParentManagementEmail.html.twig', [
      'signedUrl' => '#preview-link',
      'childCount' => 1,
      'usernames' => [$user->getUsername()],
    ]);
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
