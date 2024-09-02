<?php

declare(strict_types=1);

namespace App\Api\Services\Search;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\Projects\ProjectsResponseManager;
use App\Api\Services\ResponseCache\ResponseCacheManager;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use OpenAPI\Server\Model\BasicUserDataResponse;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Model\SearchResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SearchResponseManager extends AbstractResponseManager
{
  public function __construct(
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    ResponseCacheManager $response_cache_manager,
    protected ProjectsResponseManager $projectsResponseManager,
  ) {
    parent::__construct($translator, $serializer, $response_cache_manager);
  }

  public function getSearchResponse(array $projects_response, array $users_response): SearchResponse
  {
    return new SearchResponse([
      'projects' => $projects_response['projects'],
      'projects_total' => $projects_response['projects_total'],
      'users' => $users_response['users'],
      'users_total' => $users_response['users_total'],
    ]);
  }

  public function getUsersSearchResponse(array $users, int $total): array
  {
    $users_data_response = [];
    $users_data_response['users'] = [];
    $users_data_response['users_total'] = $total;

    foreach ($users as $user) {
      $user_data = $this->getBasicUserDataResponse($user);
      $users_data_response['users'][] = $user_data;
    }

    return $users_data_response;
  }

  public function getBasicUserDataResponse(User $user): BasicUserDataResponse
  {
    return new BasicUserDataResponse([
      'id' => $user->getId(),
      'username' => $user->getUsername(),
      'projects' => $user->getPrograms()->count(),
      'followers' => $user->getFollowers()->count(),
      'following' => $user->getFollowing()->count(),
    ]);
  }

  public function getProjectsSearchResponse(array $projects, int $total): array
  {
    $projects_data_response = [];
    $projects_data_response['projects'] = [];
    $projects_data_response['projects_total'] = $total;

    /** @var Program $project */
    foreach ($projects as $project) {
      $project_data = $this->getProjectDataResponse($project);
      $projects_data_response['projects'][] = $project_data;
    }

    return $projects_data_response;
  }

  public function getProjectDataResponse(Program $project): ProjectResponse
  {
    return $this->projectsResponseManager->createProjectDataResponse($project, null);
  }
}
