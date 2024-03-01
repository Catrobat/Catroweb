<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Notifications\NotificationsApiFacade;
use OpenAPI\Server\Api\NotificationsApiInterface;
use OpenAPI\Server\Model\NotificationsCountResponse;
use Symfony\Component\HttpFoundation\Response;

class NotificationsApi extends AbstractApiController implements NotificationsApiInterface
{
  public function __construct(private readonly NotificationsApiFacade $facade)
  {
  }

  public function notificationIdReadPut(int $id, string $accept_language, int &$responseCode, array &$responseHeaders): void
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (is_null($user)) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $successful = $this->facade->getProcessor()->markNotificationAsSeen($id, $user);
    $responseCode = $successful ? Response::HTTP_NO_CONTENT : Response::HTTP_NOT_FOUND;
  }

  public function notificationsCountGet(int &$responseCode, array &$responseHeaders): ?NotificationsCountResponse
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (is_null($user)) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $response = $this->facade->getResponseManager()->createNotificationsCountResponse($user);

    $responseCode = Response::HTTP_OK;
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  public function notificationsGet(string $accept_language, int $limit, int $offset, string $attributes, string $type, int &$responseCode, array &$responseHeaders): array|object|null
  {
    // TODO: Implement notificationsGet() method.

    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return null;
  }

  public function notificationsReadPut(int &$responseCode, array &$responseHeaders): void
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (is_null($user)) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return;
    }

    $this->facade->getProcessor()->markAllAsSeen($user);

    $responseCode = Response::HTTP_NO_CONTENT;
  }
}
