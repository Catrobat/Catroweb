<?php

declare(strict_types=1);

namespace App\Api;

use App\DB\Entity\User\User;
use Symfony\Component\RateLimiter\RateLimiterFactory;

trait RateLimitTrait
{
  private function checkUserRateLimit(User $user, RateLimiterFactory $limiter): bool
  {
    $rate_limiter = $limiter->create($user->getId());

    return $rate_limiter->consume()->isAccepted();
  }

  private function checkIpRateLimit(string $ip, RateLimiterFactory $limiter): bool
  {
    $rate_limiter = $limiter->create($ip);

    return $rate_limiter->consume()->isAccepted();
  }

  /**
   * @param array<string, string> $responseHeaders
   */
  private function addRateLimitHeaders(array &$responseHeaders, RateLimiterFactory $limiter, string $key): void
  {
    $rate_limiter = $limiter->create($key);
    $limit = $rate_limiter->consume(0);

    $responseHeaders['X-RateLimit-Limit'] = (string) $limit->getLimit();
    $responseHeaders['X-RateLimit-Remaining'] = (string) $limit->getRemainingTokens();
    $responseHeaders['X-RateLimit-Reset'] = (string) $limit->getRetryAfter()->getTimestamp();
  }
}
