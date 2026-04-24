<?php

declare(strict_types=1);

namespace App\Api;

use App\DB\Entity\User\User;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface as RateLimiterFactory;

trait RateLimitTrait
{
  private function checkUserRateLimit(User $user, RateLimiterFactory $limiter): ?RateLimit
  {
    $rate_limit = $limiter->create($user->getId())->consume();

    return $rate_limit->isAccepted() ? $rate_limit : null;
  }

  private function checkIpRateLimit(string $ip, RateLimiterFactory $limiter): ?RateLimit
  {
    $rate_limit = $limiter->create($ip)->consume();

    return $rate_limit->isAccepted() ? $rate_limit : null;
  }

  /**
   * @param array<string, string> $responseHeaders
   */
  private function addRateLimitHeaders(array &$responseHeaders, RateLimit $rate_limit): void
  {
    $responseHeaders['X-RateLimit-Limit'] = (string) $rate_limit->getLimit();
    $responseHeaders['X-RateLimit-Remaining'] = (string) $rate_limit->getRemainingTokens();
    $responseHeaders['X-RateLimit-Reset'] = (string) $rate_limit->getRetryAfter()->getTimestamp();
  }
}
