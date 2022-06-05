<?php

namespace App\Api\Services\Base;

use App\Api\Exceptions\ApiException;
use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait PandaAuthenticationTrait.
 */
trait PandaAuthenticationTrait
{
  private string $token;

  public function setPandaAuth(?string $value): void
  {
    $this->token = $this->extractAuthenticationToken($value ?? '');
  }

  public function getAuthenticationToken(): string
  {
    return $this->token;
  }

  /**
   * @throws Exception
   */
  private function extractAuthenticationToken(string $value): string
  {
    $split = preg_split('#\s+#', $value);
    if (count($split) < 2 || empty($split[1])) {
      throw new ApiException('The route must be registered under the jwt_token_authenticator! (security.yaml)', Response::HTTP_UNAUTHORIZED);
    }

    return strval($split[1]);
  }
}
