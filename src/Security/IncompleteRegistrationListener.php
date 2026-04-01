<?php

declare(strict_types=1);

namespace App\Security;

use App\DB\Entity\User\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsEventListener(event: KernelEvents::REQUEST, priority: -20)]
readonly class IncompleteRegistrationListener
{
  private const array ALLOWED_ROUTES = [
    'complete_registration',
    'complete_registration_submit',
    'verify_email_pending',
    'verify_resend',
    'consent_pending',
    'consent_change_parent_email',
    'consent_resend',
    'parental_consent_confirm',
    'request_registration_confirmation_route',
    'registration_confirmation_route',
    'logout',
    'parent_info',
    'parent_portal',
    'parent_send_link',
    'parent_management',
    'parent_grant_consent',
    'parent_revoke_consent',
    'parent_delete_account',
    'parent_request_export',
    '',
  ];

  public function __construct(
    private TokenStorageInterface $token_storage,
    private UrlGeneratorInterface $url_generator,
  ) {
  }

  public function __invoke(RequestEvent $event): void
  {
    if (!$event->isMainRequest()) {
      return;
    }

    $route = $event->getRequest()->attributes->get('_route', '');
    if (in_array($route, self::ALLOWED_ROUTES, true)) {
      return;
    }

    $path = $event->getRequest()->getPathInfo();
    if (str_starts_with($path, '/api/') || str_starts_with($path, '/_')) {
      return;
    }

    $token = $this->token_storage->getToken();
    if (null === $token) {
      return;
    }

    $user = $token->getUser();
    if (!$user instanceof User) {
      return;
    }

    if (null === $user->getDateOfBirth()) {
      $event->setResponse(new RedirectResponse($this->url_generator->generate('complete_registration')));

      return;
    }

    if (!$user->isVerified()) {
      $event->setResponse(new RedirectResponse($this->url_generator->generate('verify_email_pending')));

      return;
    }

    if (in_array($user->getConsentStatus(), ['pending', 'revoked'], true)) {
      $event->setResponse(new RedirectResponse($this->url_generator->generate('consent_pending')));
    }
  }
}
