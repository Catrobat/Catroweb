<?php

namespace App\Api;

use App\Catrobat\Services\FeaturedImageRepository;
use App\Catrobat\Services\Formatter\ElapsedTimeStringFormatter;
use App\Entity\FeaturedProgram;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Repository\FeaturedRepository;
use Exception;
use OpenAPI\Server\Api\ProjectsApiInterface;
use OpenAPI\Server\Model\FeaturedProject;
use OpenAPI\Server\Model\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProjectsApi extends AbstractController implements ProjectsApiInterface
{
  private string $token;

  private ProgramManager $program_manager;

  private SessionInterface $session;

  private ElapsedTimeStringFormatter $time_formatter;

  private FeaturedRepository $featured_repository;

  private FeaturedImageRepository $featured_image_repository;

  public function __construct(ProgramManager $program_manager, SessionInterface $session,
                              ElapsedTimeStringFormatter $time_formatter, FeaturedRepository $featured_repository,
                              FeaturedImageRepository $featured_image_repository)
  {
    $this->program_manager = $program_manager;
    $this->session = $session;
    $this->time_formatter = $time_formatter;
    $this->featured_repository = $featured_repository;
    $this->featured_image_repository = $featured_image_repository;
  }

  /**
   * {@inheritdoc}
   */
  public function setPandaAuth($value): void
  {
    $this->token = preg_split('#\s+#', $value)[1];
  }

  /**
   * {@inheritdoc}
   */
  public function projectProjectIdGet(string $project_id, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectProjectIdGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsFeaturedGet(string $platform = null, string $maxVersion = null, ?int $limit = 20, ?int $offset = 0, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $programs = $this->featured_repository->getFeaturedPrograms($flavor, $limit, $offset, $platform, $maxVersion);
    $responseCode = Response::HTTP_OK;

    $featured_programs = [];

    /** @var FeaturedProgram $featured_program */
    foreach ($programs as &$featured_program)
    {
      $result = [
        'id' => $featured_program->getId(),
        'name' => $featured_program->getProgram()->getName(),
        'author' => $featured_program->getProgram()->getUser()->getUsername(),
        'featured_image' => $this->featured_image_repository->getAbsoluteWWebPath($featured_program->getId(), $featured_program->getImageType()),
      ];
      $new_featured_project = new FeaturedProject($result);
      $featured_programs[] = $new_featured_project;
    }

    return $featured_programs;
  }

  /**
   * {@inheritdoc}
   */
  public function projectsGet(string $project_type, ?string $accept_language = null, ?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    if (null === $max_version)
    {
      $max_version = '0';
    }
    if (null === $limit)
    {
      $limit = 20;
    }
    if (null === $offset)
    {
      $offset = 0;
    }
    if (null === $accept_language)
    {
      $accept_language = 'en';
    }

    $programs = $this->program_manager->getProjects($project_type, $max_version, $limit, $offset, $flavor);
    $responseData = $this->getProjectsResponseData($programs);
    $responseCode = Response::HTTP_OK;

    return $responseData;
  }

  /**
   * {@inheritdoc}
   */
  public function projectsPost(string $checksum, UploadedFile $file, ?string $accept_language = null, ?string $flavor = null, ?string $tag1 = null, ?string $tag2 = null, ?string $tag3 = null, ?bool $private = null, &$responseCode, array &$responseHeaders)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function projectsSearchGet(string $query_string, ?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectsSearchGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsUserGet(?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    if (null === $max_version)
    {
      $max_version = '0';
    }
    if (null === $limit)
    {
      $limit = 20;
    }
    if (null === $offset)
    {
      $offset = 0;
    }
    $jwtPayload = $this->program_manager->decodeToken($this->token);
    if (!array_key_exists('username', $jwtPayload))
    {
      return [];
    }
    $programs = $this->program_manager->getAuthUserPrograms($jwtPayload['username'], $limit, $offset, $flavor, $max_version);
    $responseData = $this->getProjectsResponseData($programs);
    $responseCode = Response::HTTP_OK;

    return $responseData;
  }

  /**
   * {@inheritdoc}
   */
  public function projectsUserUserIdGet(string $user_id, ?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    if (null == $max_version)
    {
      $max_version = '0';
    }
    if (null == $limit)
    {
      $limit = 20;
    }
    if (null == $offset)
    {
      $offset = 0;
    }
    $programs = $this->program_manager->getUserPublicPrograms($user_id, $limit, $offset, $flavor, $max_version);
    $responseData = $this->getProjectsResponseData($programs);
    $responseCode = Response::HTTP_OK;

    return $responseData;
  }

  /**
   * @throws Exception
   */
  private function getProjectsResponseData(array $programs): array
  {
    $projects = [];
    foreach ($programs as &$program)
    {
      $result = [
        'id' => $program->getId(),
        'name' => $program->getName(),
        'author' => $program->getUser()->getUserName(),
        'description' => $program->getDescription(),
        'version' => $program->getCatrobatVersionName(),
        'views' => $program->getViews(),
        'download' => $program->getDownloads(),
        'private' => $program->getPrivate(),
        'flavor' => $program->getFlavor(),
        'uploaded' => $program->getUploadedAt()->getTimestamp(),
        'uploaded_string' => $this->time_formatter->getElapsedTime($program->getUploadedAt()->getTimestamp()),
        'screenshot_large' => $this->program_manager->getScreenshotLarge($program->getId()),
        'screenshot_small' => $this->program_manager->getScreenshotSmall($program->getId()),
        'project_url' => ltrim($this->generateUrl(
          'program',
          [
            'flavor' => $this->session->get('flavor_context'),
            'id' => $program->getId(),
          ],
          UrlGeneratorInterface::ABSOLUTE_URL), '/'
        ),
        'download_url' => ltrim($this->generateUrl(
          'download',
          [
            'id' => $program->getId(),
          ],
          UrlGeneratorInterface::ABSOLUTE_URL), '/'),
        'filesize' => $program->getFilesize() / 1_048_576,
      ];
      $project = new Project($result);
      $projects[] = $project;
    }

    return $projects;
  }
}
