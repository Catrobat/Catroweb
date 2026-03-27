<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Achievements\AchievementsApiFacade;
use App\Api\Services\Base\AbstractApiController;
use App\User\UserManager;
use OpenAPI\Server\Api\AchievementsApiInterface;
use OpenAPI\Server\Model\AchievementsCountResponse;
use OpenAPI\Server\Model\AchievementsListResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class AchievementsApi extends AbstractApiController implements AchievementsApiInterface
{
  use RateLimitTrait;

  public function __construct(
    private readonly AchievementsApiFacade $facade,
    private readonly UserManager $user_manager,
    private readonly RateLimiterFactory $achievementBurstLimiter,
  ) {
  }

  #[\Override]
  public function achievementsGet(string $accept_language, int &$responseCode, array &$responseHeaders): ?AchievementsListResponse
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof \App\DB\Entity\User\User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    if (!$this->checkUserRateLimit($user, $this->achievementBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $this->addRateLimitHeaders($responseHeaders, $this->achievementBurstLimiter, $user->getId());

    $page_data = $this->facade->getLoader()->getAchievementsPageData($user);

    $response = $this->facade->getResponseManager()->createAchievementsListResponse(
      $page_data['unlocked'],
      $page_data['locked'],
      $page_data['most_recent'],
      $page_data['total_count'],
      $page_data['unlocked_count'],
      $accept_language,
    );

    $responseCode = Response::HTTP_OK;
    $this->facade->getResponseManager()->addResponseHashToHeaders($responseHeaders, $response);
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $response;
  }

  #[\Override]
  public function achievementsCountGet(int &$responseCode, array &$responseHeaders): ?AchievementsCountResponse
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof \App\DB\Entity\User\User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    if (!$this->checkUserRateLimit($user, $this->achievementBurstLimiter)) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $this->addRateLimitHeaders($responseHeaders, $this->achievementBurstLimiter, $user->getId());

    $count = $this->facade->getLoader()->getUnseenCount($user);
    $response = $this->facade->getResponseManager()->createAchievementsCountResponse($count);

    $responseCode = Response::HTTP_OK;

    return $response;
  }

  #[\Override]
  public function achievementsReadPut(int &$responseCode, array &$responseHeaders): void
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof \App\DB\Entity\User\User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $this->facade->getProcessor()->markAllAsSeen($user);

    $responseCode = Response::HTTP_NO_CONTENT;
  }

  #[\Override]
  public function userIdAchievementsGet(string $id, string $accept_language, int &$responseCode, array &$responseHeaders): array|object|null
  {
    $user = $this->user_manager->find($id);
    if (!$user instanceof \App\DB\Entity\User\User) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $unlocked = $this->facade->getLoader()->getUnlockedAchievements($user);
    $responses = $this->facade->getResponseManager()->createAchievementResponseList($unlocked, $accept_language);

    $responseCode = Response::HTTP_OK;
    $this->facade->getResponseManager()->addContentLanguageToHeaders($responseHeaders);

    return $responses;
  }
}
