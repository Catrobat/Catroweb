<?php

namespace App\Api\Services\Projects;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\Base\TranslatorAwareTrait;
use App\Api\Services\ResponseCache\ResponseCacheManager;
use App\DB\Entity\Project\Extension;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Special\FeaturedProgram;
use App\DB\Entity\Project\Tag;
use App\Project\ProgramManager;
use App\Storage\ImageRepository;
use App\Utils\ElapsedTimeStringFormatter;
use Exception;
use OpenAPI\Server\Model\FeaturedProjectResponse;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Model\ProjectsCategory;
use OpenAPI\Server\Model\TagResponse;
use OpenAPI\Server\Model\UploadErrorResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProjectsResponseManager extends AbstractResponseManager
{
  use TranslatorAwareTrait;

  public function __construct(
    private readonly ElapsedTimeStringFormatter $time_formatter,
    private readonly ImageRepository $image_repository,
    private readonly UrlGeneratorInterface $url_generator,
    private readonly ParameterBagInterface $parameter_bag,
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    private readonly ProgramManager $project_manager,
    ResponseCacheManager $response_cache_manager
  ) {
    parent::__construct($translator, $serializer, $response_cache_manager);
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
      'author' => $project->getUser()->getUserIdentifier(),
      'description' => $project->getDescription(),
      'version' => $project->getCatrobatVersionName(),
      'views' => $project->getViews(),
      'downloads' => $project->getDownloads(),
      'reactions' => 0,
      'comments' => 0,
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
        'open_api_server_projects_projectidcatrobatget',
        [
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
      'author' => $featured_project->getProgram()->getUser()->getUserIdentifier(),
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

  public function createProjectsExtensionsResponse(array $extensions, string $locale): array
  {
    $response = [];

    /** @var Extension $extension */
    foreach ($extensions as $extension) {
      $response[] = $this->createExtensionResponse($extension, $locale);
    }

    return $response;
  }

  public function createExtensionResponse(Extension $extension, string $locale): TagResponse
  {
    return new TagResponse([
      'id' => $extension->getInternalTitle(),
      'text' => $this->__($extension->getTitleLtmCode(), [], $locale),
    ]);
  }

  public function createProjectsTagsResponse(array $tags, string $locale): array
  {
    $response = [];
    /** @var Tag $tag */
    foreach ($tags as $tag) {
      $response[] = $this->createTagResponse($tag, $locale);
    }

    return $response;
  }

  public function createTagResponse(Tag $tag, string $locale): TagResponse
  {
    return new TagResponse([
      'id' => $tag->getInternalTitle(),
      'text' => $this->__($tag->getTitleLtmCode(), [], $locale),
    ]);
  }

  public function createProjectCatrobatFileResponse(string $id, File $file): BinaryFileResponse
  {
    $response = new BinaryFileResponse($file);
    $response->headers->set(
      'Content-Disposition',
      'attachment; filename="'.$id.'.catrobat"'
    );

    return $response;
  }
}
