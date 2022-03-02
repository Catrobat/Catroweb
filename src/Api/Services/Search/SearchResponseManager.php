<?php

namespace App\Api\Services\Search;

use App\Api\Services\Base\AbstractResponseManager;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\Project\ProgramManager;
use App\Storage\ImageRepository;
use OpenAPI\Server\Model\BasicUserDataResponse;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Model\SearchResponse;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SearchResponseManager extends AbstractResponseManager
{
  private UrlGeneratorInterface $url_generator;
  private ParameterBagInterface $parameter_bag;
  private ImageRepository $imageRepository;
  private ProgramManager $programManager;

  public function __construct(
        TranslatorInterface $translator, UrlGeneratorInterface $url_generator, ParameterBagInterface $parameter_bag, ImageRepository $imageRepository, ProgramManager $programManager
    ) {
    parent::__construct($translator);
    $this->url_generator = $url_generator;
    $this->parameter_bag = $parameter_bag;
    $this->imageRepository = $imageRepository;
    $this->programManager = $programManager;
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

  public function getUsersSearchResponse(array $users, $total): array
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

  public function getProjectsSearchResponse(array $projects, $total): array
  {
    $projects_data_response = [];
    $projects_data_response['projects'] = [];
    $projects_data_response['projects_total'] = $total;

    foreach ($projects as $project) {
      $project_data = $this->getProjectDataResponse($project);
      $projects_data_response['projects'][] = $project_data;
    }

    return $projects_data_response;
  }

  public function getProjectDataResponse($program): ProjectResponse
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
      'screenshot_large' => $program->isExample() ? $this->imageRepository->getAbsoluteWebPath($program->getId(), $program->getImageType(), false) : $this->programManager->getScreenshotLarge($project->getId()),
      'screenshot_small' => $program->isExample() ? $this->imageRepository->getAbsoluteWebPath($program->getId(), $program->getImageType(), false) : $this->programManager->getScreenshotSmall($project->getId()),
      'project_url' => ltrim($this->generateUrl(
          'program',
          [
            'theme' => $this->parameter_bag->get('umbrellaTheme'),
            'id' => $project->getId(),
          ],
          UrlGeneratorInterface::ABSOLUTE_URL), '/'
      ),
      'download_url' => ltrim($this->url_generator->generateUrl(
          'download',
          [
            'theme' => $this->parameter_bag->get('umbrellaTheme'),
            'id' => $project->getId(),
          ],
          UrlGeneratorInterface::ABSOLUTE_URL), '/'),
      'filesize' => ($project->getFilesize() / 1_048_576),
    ]);
  }
}
