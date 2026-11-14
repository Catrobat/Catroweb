<?php

declare(strict_types=1);

namespace App\System\Log;

use Bugsnag\Report;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Bugsnag callback: an access denial for a caller who is not (fully) logged in is the normal way to a 401 / login
 * redirect, so it is not reported. A logged-in user being denied is a real authorization failure and still is.
 * Same check as Symfony's security ExceptionListener, which runs after Bugsnag's kernel.exception listener.
 */
final readonly class IgnoreLoginRequiredAccessDenied
{
  public function __construct(
    private TokenStorageInterface $token_storage,
    #[Autowire(service: 'security.authentication.trust_resolver')]
    private AuthenticationTrustResolverInterface $trust_resolver,
  ) {
  }

  public function __invoke(Report $report): ?bool
  {
    if ($report->getOriginalError() instanceof AccessDeniedException
      && !$this->trust_resolver->isFullFledged($this->token_storage->getToken())) {
      return false;
    }

    return null;
  }
}
