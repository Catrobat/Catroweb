<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Notifications\NotificationsApiFacade;
use OpenAPI\Server\Api\NotificationsApiInterface;
use OpenAPI\Server\Model\NotificationListResponse;
use OpenAPI\Server\Model\NotificationsCountResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class NotificationsApi extends AbstractApiController implements NotificationsApiInterface
{
  use RateLimitTrait;

  private const int DEFAULT_LIMIT = 20;
  private const int MAX_LIMIT = 50;

  public function __construct(
    private readonly NotificationsApiFacade $facade,
    private readonly RateLimiterFactory $notificationBurstLimiter,
  ) {
  }

  #[\Override]
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

  #[\Override]
  public function notificationsCountGet(int &$responseCode, array &$responseHeaders): ?NotificationsCountResponse
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (is_null($user)) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    if (!$this->checkUserRateLimit($user, $this->notificationBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $this->addRateLimitHeaders($responseHeaders, $this->notificationBurstLimiter, $user->getId());

    $response = $this->facade->getResponseManager()->createNotificationsCountResponse($user);

    $responseCode = Response::HTTP_OK;
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function notificationsGet(string $accept_language, int $limit, ?string $cursor, string $type, int &$responseCode, array &$responseHeaders): ?NotificationListResponse
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (is_null($user)) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    if (!$this->checkUserRateLimit($user, $this->notificationBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $this->addRateLimitHeaders($responseHeaders, $this->notificationBurstLimiter, $user->getId());

    $limit = $this->normalizeLimit($limit);

    $cursor_id = null;
    if (null !== $cursor) {
      $decoded = base64_decode($cursor, true);
      if (false === $decoded || !ctype_digit($decoded)) {
        $responseCode = Response::HTTP_BAD_REQUEST;

        return null;
      }
      $cursor_id = (int) $decoded;
    }

    $page_data = $this->facade->getLoader()->getNotificationsPage($user, $type, $limit, $cursor_id);

    $response = $this->facade->getResponseManager()->createNotificationListResponse(
      $page_data['notifications'],
      $page_data['has_more'],
      $user,
    );

    $responseCode = Response::HTTP_OK;
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
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

  private function normalizeLimit(int $limit): int
  {
    $limit = $limit > 0 ? $limit : self::DEFAULT_LIMIT;

    return min($limit, self::MAX_LIMIT);
  }
}
