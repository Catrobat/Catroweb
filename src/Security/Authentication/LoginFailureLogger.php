<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use App\User\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

/**
 * One line per failed username/password login, to measure whether people type their email
 * where only the username is accepted. Never logs what was typed: only booleans about it.
 * The "login" channel has its own handler in prod, so the line is not swallowed by fingers_crossed.
 */
#[AsEventListener]
class LoginFailureLogger
{
  /** Line format of the "login" channel's handler: context only, no ip/user extras. */
  public const string LOG_FORMAT = "[%datetime%] %level_name% %channel%: %message% %context%\n";

  public function __construct(
    private readonly UserManager $user_manager,
    private readonly LoggerInterface $loginLogger,
  ) {
  }

  public function __invoke(LoginFailureEvent $event): void
  {
    $passport = $event->getPassport();
    // OAuth, WebView tokens and refresh tokens carry no typed identifier: not what we measure.
    if (null === $passport || !$passport->hasBadge(PasswordCredentials::class)) {
      return;
    }

    /** @var UserBadge $badge */
    $badge = $passport->getBadge(UserBadge::class);
    $input = $badge->getUserIdentifier();
    $looks_like_email = str_contains($input, '@');

    $context = [
      'event' => 'login_failed',
      'firewall' => $event->getFirewallName(),
      'path' => $event->getRequest()->getPathInfo(),
      'input_looks_like_email' => $looks_like_email,
      'user_exists' => null !== $this->user_manager->findUserByUsername($input),
    ];
    if ($looks_like_email) {
      // Would accepting the email have found an account?
      $context['email_is_registered'] = null !== $this->user_manager->findUserByEmail($input);
    }

    $this->loginLogger->info('login_failed', $context);
  }
}
