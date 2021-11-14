<?php

namespace App\Api\Services\Projects;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\Base\TranslatorAwareTrait;
use App\Catrobat\Services\ImageRepository;
use App\Entity\FeaturedProgram;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\Tag;
use App\Utils\ElapsedTimeStringFormatter;
use Exception;
use OpenAPI\Server\Model\FeaturedProjectResponse;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Model\ProjectsCategory;
use OpenAPI\Server\Model\UploadErrorResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProjectsResponseManager extends AbstractResponseManager
{
  use TranslatorAwareTrait;

  private ElapsedTimeStringFormatter $time_formatter;
  private UrlGeneratorInterface $url_generator;
  private ImageRepository $image_repository;
  private ParameterBagInterface $parameter_bag;
  private ProgramManager $project_manager;

  public function __construct(
    ElapsedTimeStringFormatter $time_formatter,
    ImageRepository $image_repository,
    UrlGeneratorInterface $url_generator,
    ParameterBagInterface $parameter_bag,
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    ProgramManager $project_manager
  ) {
    parent::__construct($translator, $serializer);
    $this->time_formatter = $time_formatter;
    $this->image_repository = $image_repository;
    $this->url_generator = $url_generator;
    $this->parameter_bag = $parameter_bag;
    $this->project_manager = $project_manager;
  }

  /**
   * @param mixed $program
   *
   * @throws Exception
   */
  public function createProjectDataResponse($program): ProjectResponse
  {
    /** @var Program $project */
    $project = $program->isExample() ? $program->getProgram() : $program;

    $tags = [];
    $project_tags = $project->getTags();
    /** @var Tag $tag */
    foreach ($project_tags as $tag) {
      $tags[$tag->getId()] = $tag->getInternalTitle();
    }

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
      'tags' => $tags,
      'uploaded' => $project->getUploadedAt()->getTimestamp(),
      'uploaded_string' => $this->time_formatter->getElapsedTime($project->getUploadedAt()->getTimestamp()),
      'screenshot_large' => $program->isExample() ? $this->image_repository->getAbsoluteWebPath($program->getId(), $program->getImageType(), false) : $this->project_manager->getScreenshotLarge($project->getId()),
      'screenshot_small' => $program->isExample() ? $this->image_repository->getAbsoluteWebPath($program->getId(), $program->getImageType(), false) : $this->project_manager->getScreenshotSmall($project->getId()),
      'project_url' => ltrim($this->url_generator->generate(
        'program',
        [
          'theme' => $this->parameter_bag->get('umbrellaTheme'),
          'id' => $project->getId(),
        ],
        UrlGeneratorInterface::ABSOLUTE_URL), '/'
      ),
      'download_url' => ltrim($this->url_generator->generate(
        'download',
        [
          'theme' => $this->parameter_bag->get('umbrellaTheme'),
          'id' => $project->getId(),
        ],
        UrlGeneratorInterface::ABSOLUTE_URL), '/'),
      'filesize' => ($project->getFilesize() / 1_048_576),
    ]);
  }

  /**
   * @throws Exception
   */
  public function createProjectsDataResponse(array $projects): array
  {
    $response = [];
    foreach ($projects as $project) {
      $response[] = $this->createProjectDataResponse($project);
    }

    return $response;
  }

  public function createFeaturedProjectResponse(FeaturedProgram $featured_project): FeaturedProjectResponse
  {
    $url = $featured_project->getUrl();
    $project_url = ltrim($this->url_generator->generate(
        'program',
        [
          'theme' => $this->parameter_bag->get('umbrellaTheme'),
          'id' => $featured_project->getProgram()->getId(),
        ],
        UrlGeneratorInterface::ABSOLUTE_URL), '/'
      );

    if (empty($url)) {
      $url = $project_url;
    } else {
      $project_url = null;
    }

    return new FeaturedProjectResponse([
      'id' => $featured_project->getId(),
      'project_id' => $featured_project->getProgram()->getId(),
      'project_url' => $project_url,
      'url' => $url,
      'name' => $featured_project->getProgram()->getName(),
      'author' => $featured_project->getProgram()->getUser()->getUsername(),
      'featured_image' => $this->image_repository->getAbsoluteWebPath($featured_project->getId(), $featured_project->getImageType(), true),
    ]);
  }

  public function createFeaturedProjectsResponse(array $featured_projects): array
  {
    $response = [];

    /** @var FeaturedProgram $featured_project */
    foreach ($featured_projects as $featured_project) {
      $response[] = $this->createFeaturedProjectResponse($featured_project);
    }

    return $response;
  }

  public function createProjectCategoryResponse(array $projects, string $category, string $locale): ProjectsCategory
  {
    return new ProjectsCategory([
      'projects_list' => $this->createProjectsDataResponse($projects),
      'type' => $category,
      'name' => $this->__('category.'.$category, [], $locale),
    ]);
  }

  public function createProjectLocation(Program $project): string
  {
    return $this->url_generator->generate(
      'program',
      [
        'theme' => $this->parameter_bag->get('umbrellaTheme'),
        'id' => $project->getId(),
      ],
      UrlGenerator::ABSOLUTE_URL);
  }

  public function createUploadErrorResponse(string $locale): UploadErrorResponse
  {
    return new UploadErrorResponse([
      'error' => $this->__('api.projectsPost.creating_error', [], $locale),
    ]);
  }
}
