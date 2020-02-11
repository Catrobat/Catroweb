<?php

namespace App\Api;

use App\Catrobat\Services\Formatter\ElapsedTimeStringFormatter;
use App\Entity\Program;
use App\Entity\ProgramManager;
use OpenAPI\Server\Api\ProjectsApiInterface;
use OpenAPI\Server\Model\Flavor;
use OpenAPI\Server\Model\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


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
   * ProjectsApi constructor.
   *
   * @param ProgramManager             $program_manager
   *
   * @param SessionInterface           $session
   *
   * @param ElapsedTimeStringFormatter $time_formatter
   */
  public function __construct(ProgramManager $program_manager, SessionInterface $session,
                              ElapsedTimeStringFormatter $time_formatter)
  {
    $this->program_manager = $program_manager;
    $this->session = $session;
    $this->time_formatter = $time_formatter;
  }

  /**
   * @inheritDoc
   */
  public function projectProjectIdGet($projectId, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectProjectIdGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsFeaturedGet($platform = null, $maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsFeaturedGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsMostDownloadedGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsMostDownloadedGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsMostViewedGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    if ($maxVersion == null)
    {
      $maxVersion = "0";
    }
    $programs = $this->program_manager->getMostViewedPrograms($flavor, $limit, $offset, $maxVersion);
    $responseData = $this->getResponseData($programs);
    $responseCode = Response::HTTP_OK;

    return $responseData;
  }

  /**
   * @inheritDoc
   */
  public function projectsPublicUserUserIdGet($userId, $maxVersion = null, $limit = 20, $offset = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsPublicUserUserIdGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsRandomProgramsGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsRandomProgramsGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsRecentGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsRecentGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsSearchGet($maxVersion = null, $limit = 20, $offset = 0, Flavor $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsSearchGet() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsUploadPost($token, $checksum = null, UploadedFile $file = null, Flavor $flavor = null, array $tags = null, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsUploadPost() method.
  }

  /**
   * @inheritDoc
   */
  public function projectsUserUserIdGet($userId, $maxVersion = null, $limit = 20, $offset = null, $token, &$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
    // TODO: Implement projectsUserUserIdGet() method.
  }

  /**
   * @param Program[] $programs
   *
   * @return Project[]
   */
  public function getResponseData($programs)
  {
    $projects = [];
    foreach ($programs as &$program)
    {
      $result = [
        'id'              => $program->getId(),
        'name'            => $program->getName(),
        'author'          => $program->getUser()->getUserName(),
        'description'     => $program->getDescription(),
        'version'         => $program->getCatrobatVersionName(),
        'views'           => $program->getViews(),
        'download'        => $program->getDownloads(),
        'private'         => $program->getPrivate(),
        'flavor'          => $program->getFlavor(),
        'uploaded'        => $program->getUploadedAt()->getTimestamp(),
        'uploadedString'  => $this->time_formatter->getElapsedTime($program->getUploadedAt()
          ->getTimestamp()),
        'screenshotLarge' => $this->program_manager->getScreenshotLarge($program->getId()),
        'screenshotSmall' => $this->program_manager->getScreenshotSmall($program->getId()),
        'projectUrl'      => ltrim($this->generateUrl('program', [
          'flavor' => $this->session->get('flavor_context'),
          'id'     => $program->getId(),
        ]), '/'),
        'downloadUrl'     => ltrim($this->generateUrl('download', [
          'id' => $program->getId(),
        ]), '/'),
        'filesize'        => $program->getFilesize() / 1048576,
      ];
      $project = new Project($result);
      array_push($projects, $project);
    }

    return $projects;
  }
}