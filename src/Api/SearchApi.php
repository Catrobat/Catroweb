<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Search\SearchApiFacade;
use App\Entity\Program;
use App\Entity\User;
use OpenAPI\Server\Api\SearchApiInterface;
use OpenAPI\Server\Model\BasicUserDataResponse;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Model\SearchResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SearchApi extends AbstractApiController implements SearchApiInterface
{
  private SearchApiFacade $facade;

  public function __construct(SearchApiFacade $facade)
  {
    $this->facade = $facade;
  }

  /**
   * {@inheritDoc}
   */
  public function searchGet(string $query, ?string $type = 'all', ?int $limit = 20, ?int $offset = 0, &$responseCode = null, array &$responseHeaders = null)
  {
    $type = $type ?? 'all';
    $limit = $this->getDefaultLimitOnNull($limit);
    $offset = $this->getDefaultOffsetOnNull($offset);

    if ('' === $query || ctype_space($query)) {
      return [];
    }

    switch ($type) {
          case 'projects':
              $projects = $this->facade->getProgramManager()->search($query, $limit, $offset);
              $projects_total = $this->facade->getProgramManager()->searchCount($query);

              $result = $this->getProjectsSearchResponse($projects, $projects_total);
              break;
          case 'users':
              $users = $this->facade->getUserManager()->search($query, $limit, $offset);
              $users_total = $this->facade->getUserManager()->searchCount($query);
              $result = $this->getUsersSearchResponse($users, $users_total);
              break;
          case 'all':
          default:
              $projects = $this->facade->getProgramManager()->search($query, $limit, $offset);
              $projects_total = $this->facade->getProgramManager()->searchCount($query);
              $projects_response = $this->getProjectsSearchResponse($projects, $projects_total);

              $users = $this->facade->getUserManager()->search($query, $limit, $offset);
              $users_total = $this->facade->getUserManager()->searchCount($query);
              $users_response = $this->getUsersSearchResponse($users, $users_total);

              $result = $this->getSearchResponse($projects_response, $users_response);
              break;
      }

    $responseHeaders['X-Response-Hash'] = md5(json_encode($result));

    return $result;
  }

    private function getSearchResponse(array $projects_response, array $users_response): SearchResponse
    {
        return new SearchResponse([
            'projects' => $projects_response['projects'],
            'projects_total' => $projects_response['projects_total'],
            'users' => $users_response['users'],
            'users_total' => $users_response['users_total'],
        ]);
    }

    private function getUsersSearchResponse(array $users, $total): array
    {
        $users_data_response = [];
        $users_data_response['users'] = [];
        $users_data_response['users_total'] = $total;

        foreach ($users as $user)
        {
            $user_data = $this->getBasicUserDataResponse($user);
            $users_data_response['users'][] = $user_data;
        }

        return $users_data_response;
    }

    private function getBasicUserDataResponse(User $user): BasicUserDataResponse
    {
        return new BasicUserDataResponse([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'projects' => $user->getPrograms()->count(),
            'followers' => $user->getFollowers()->count(),
            'following' => $user->getFollowing()->count(),
        ]);
    }

    private function getProjectsSearchResponse(array $projects, $total): array
    {
        $projects_data_response = [];
        $projects_data_response['projects'] = [];
        $projects_data_response['projects_total'] = $total;

        foreach ($projects as $project)
        {
            $project_data = $this->getProjectDataResponse($project);
            $projects_data_response['projects'][] = $project_data;
        }

        return $projects_data_response;
    }

    private function getProjectDataResponse($program): ProjectResponse
    {
        /** @var Program $project */
        $project = $program->isExample() ? $program->getProgram() : $program;

        return new ProjectResponse([
            'id' => $project->getId(),
            'name' => $project->getName(),
            'author' => $project->getUser()->getUserName(),
            'description' => $project->getDescription(),
            'version' => $project->getCatrobatVersionName(),
            'views' => $project->getViews(),
            'download' => $project->getDownloads(),
            'private' => $project->getPrivate(),
            'flavor' => $project->getFlavor(),
            'tags' => $project->getTagsName(),
            'uploaded' => $project->getUploadedAt()->getTimestamp(),
            'uploaded_string' => $this->facade->getTimeFormatter()->getElapsedTime($project->getUploadedAt()->getTimestamp()),
            'screenshot_large' => $program->isExample() ? $this->facade->getImageRepository()->getAbsoluteWebPath($program->getId(), $program->getImageType(), false) : $this->facade->getProgramManager()->getScreenshotLarge($project->getId()),
            'screenshot_small' => $program->isExample() ? $this->facade->getImageRepository()->getAbsoluteWebPath($program->getId(), $program->getImageType(), false) : $this->facade->getProgramManager()->getScreenshotSmall($project->getId()),
            'project_url' => ltrim($this->generateUrl(
                'program',
                [
                    'theme' => $this->facade->getParameterBag()->get('umbrellaTheme'),
                    'id' => $project->getId(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL), '/'
            ),
            'download_url' => ltrim($this->generateUrl(
                'download',
                [
                    'theme' => $this->facade->getParameterBag()->get('umbrellaTheme'),
                    'id' => $project->getId(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL), '/'),
            'filesize' => ($project->getFilesize() / 1_048_576),
        ]);
    }


}
