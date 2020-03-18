<?php

namespace App\Api;

use App\Catrobat\Services\Formatter\ElapsedTimeStringFormatter;
use App\Entity\Program;
use App\Entity\ProgramManager;
use OpenAPI\Server\Api\ProjectsApiInterface;
use OpenAPI\Server\Model\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProjectsApi extends AbstractController implements ProjectsApiInterface
{
  /**
   * @var ProgramManager
   */
  private $program_manager;

  /**
   * @var SessionInterface
   */
  private $session;

  /**
   * @var ElapsedTimeStringFormatter
   */
  private $time_formatter;

  /**
   * @var string
   */
  private $token;

  /**
   * ProjectsApi constructor.
   */
  public function __construct(ProgramManager $program_manager, SessionInterface $session,
                              ElapsedTimeStringFormatter $time_formatter)
  {
    $this->program_manager = $program_manager;
    $this->session = $session;
    $this->time_formatter = $time_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public function setPandaAuth($value)
  {
    $this->token = preg_split('/\s+/', $value)[1];
  }

  /**
   * {@inheritdoc}
   */
  public function projectProjectIdGet(string $projectId, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectProjectIdGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsFeaturedGet(string $platform = null, string $maxVersion = null, int $limit = 20, int $offset = 0, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectsFeaturedGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsGet(string $projectType, string $acceptLanguage = null, string $maxVersion = null, int $limit = 20, int $offset = 0, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectsGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsPost(string $acceptLanguage = null, string $checksum = null, UploadedFile $file = null, string $flavor = null, array $tags = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectsPost() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsSearchGet(string $queryString, string $maxVersion = null, int $limit = 20, int $offset = 0, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectsSearchGet() method.
  }

  /**
   * {@inheritdoc}
   */
  public function projectsUserGet(string $maxVersion = null, ?int $limit = 20, ?int $offset = 0, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    if (null == $maxVersion)
    {
      $maxVersion = '0';
    }
    if (null == $limit)
    {
      $limit = 20;
    }
    if (null == $offset)
    {
      $offset = 0;
    }
    $jwtPayload = $this->program_manager->decodeToken($this->token);
    if (!array_key_exists('username', $jwtPayload))
    {
      return [];
    }
    $programs = $this->program_manager->getAuthUserPrograms($jwtPayload['username'], $limit, $offset, $flavor, $maxVersion);
    $responseData = $this->getProjectsResponseData($programs);
    $responseCode = Response::HTTP_OK;

    return $responseData;
  }

  /**
   * {@inheritdoc}
   */
  public function projectsUserUserIdGet(string $userId, string $maxVersion = null, int $limit = 20, int $offset = 0, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectsUserUserIdGet() method.
  }

  /**
   * @param Program[] $programs
   *
   * @return Project[]
   */
  private function getProjectsResponseData($programs)
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
        'uploadedString' => $this->time_formatter->getElapsedTime($program->getUploadedAt()
          ->getTimestamp()),
        'screenshotLarge' => $this->program_manager->getScreenshotLarge($program->getId()),
        'screenshotSmall' => $this->program_manager->getScreenshotSmall($program->getId()),
        'projectUrl' => ltrim($this->generateUrl('program', [
          'flavor' => $this->session->get('flavor_context'),
          'id' => $program->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL), '/'),
        'downloadUrl' => ltrim($this->generateUrl('download', [
          'id' => $program->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL), '/'),
        'filesize' => $program->getFilesize() / 1048576,
      ];
      $project = new Project($result);
      array_push($projects, $project);
    }

    return $projects;
  }
}
