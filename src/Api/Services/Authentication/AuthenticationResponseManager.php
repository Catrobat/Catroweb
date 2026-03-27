<?php

declare(strict_types=1);

namespace App\Api\Services\Authentication;

use App\Api\Services\Base\AbstractResponseManager;
use App\Security\Authentication\CookieService;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationResponseManager extends AbstractResponseManager
{
  public function __construct(
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    \Psr\Cache\CacheItemPoolInterface $cache,
    private readonly CookieService $cookie_service,
  ) {
    parent::__construct($translator, $serializer, $cache);
  }

  public function createOAuthPostResponse(string $token, string $refresh_token): JWTResponse
  {
    return new JWTResponse(
      [
        'token' => $token,
        'refresh_token' => $refresh_token,
      ]
    );
  }

  public function addClearedAuthenticationCookiesToHeader(array &$responseHeaders): void
  {
    $this->cookie_service->addClearedAuthenticationCookiesToHeader($responseHeaders);
  }
}
