<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Achievements\AchievementsApiFacade;
use App\Api\Services\Base\AbstractApiController;
use App\User\UserManager;
use OpenAPI\Server\Api\AchievementsApiInterface;
use OpenAPI\Server\Model\AchievementsCountResponse;
use OpenAPI\Server\Model\AchievementsDataResponse;
use OpenAPI\Server\Model\AchievementsListResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface as RateLimiterFactory;

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

    $rate_limit = $this->checkUserRateLimit($user, $this->achievementBurstLimiter);
    if (null === $rate_limit) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $this->addRateLimitHeaders($responseHeaders, $rate_limit);

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

    $rate_limit = $this->checkUserRateLimit($user, $this->achievementBurstLimiter);
    if (null === $rate_limit) {
      $responseCode = Response::HTTP_TOO_MANY_REQUESTS;

      return null;
    }

    $this->addRateLimitHeaders($responseHeaders, $rate_limit);

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
  public function usersIdAchievementsGet(string $id, string $accept_language, int &$responseCode, array &$responseHeaders): ?AchievementsDataResponse
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

    $response = new AchievementsDataResponse();
    $response->setData($responses);

    return $response;
  }
}
