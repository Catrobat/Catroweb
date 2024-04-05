<?php

declare(strict_types=1);

namespace App\Api\Services\Authentication;

use App\Api\Services\Base\AbstractResponseManager;
use OpenAPI\Server\Model\JWTResponse;

class AuthenticationResponseManager extends AbstractResponseManager
{
  public function createOAuthPostResponse(string $token, string $refresh_token): JWTResponse
  {
    return new JWTResponse(
      [
        'token' => $token,
        'refresh_token' => $refresh_token,
      ]
    );
  }
}
