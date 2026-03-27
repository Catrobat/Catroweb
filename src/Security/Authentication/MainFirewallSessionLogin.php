<?php

declare(strict_types=1);

namespace App\Security\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

final readonly class MainFirewallSessionLogin
{
  private const string FIREWALL_NAME = 'main';
  private const string SESSION_KEY = '_security_'.self::FIREWALL_NAME;

  public function __construct(
    private SessionAuthenticationStrategyInterface $session_authentication_strategy,
  ) {
  }

  public function login(Request $request, TokenInterface $token): void
  {
    if (!$request->hasSession()) {
      return;
    }

    $user = $token->getUser();
    if (!$user instanceof UserInterface) {
      return;
    }

    // JWT cookies stay scoped to the themed web base path, so admin routes
    // still need the regular main-firewall session to see the logged-in user.
    $session_token = new UsernamePasswordToken($user, self::FIREWALL_NAME, $token->getRoleNames());
    if ($request->hasPreviousSession()) {
      $this->session_authentication_strategy->onAuthentication($request, $session_token);
    }

    $request->getSession()->set(self::SESSION_KEY, serialize($session_token));
  }
}
