<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Achievements\AchievementsApiFacade;
use App\Api\Services\Base\AbstractApiController;
use OpenAPI\Server\Api\AchievementsApiInterface;
use OpenAPI\Server\Model\AchievementsCountResponse;
use OpenAPI\Server\Model\AchievementsListResponse;
use Symfony\Component\HttpFoundation\Response;

class AchievementsApi extends AbstractApiController implements AchievementsApiInterface
{
  public function __construct(private readonly AchievementsApiFacade $facade)
  {
  }

  #[\Override]
  public function achievementsGet(string $accept_language, int &$responseCode, array &$responseHeaders): ?AchievementsListResponse
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (null === $user) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

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
    if (null === $user) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return null;
    }

    $count = $this->facade->getLoader()->getUnseenCount($user);
    $response = $this->facade->getResponseManager()->createAchievementsCountResponse($count);

    $responseCode = Response::HTTP_OK;

    return $response;
  }

  #[\Override]
  public function achievementsReadPut(int &$responseCode, array &$responseHeaders): void
  {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (null === $user) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $this->facade->getProcessor()->markAllAsSeen($user);

    $responseCode = Response::HTTP_NO_CONTENT;
  }
}
