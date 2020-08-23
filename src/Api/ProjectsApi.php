<?php

namespace App\Api;

use App\Catrobat\Requests\AddProgramRequest;
use App\Catrobat\Services\ImageRepository;
use App\Entity\ExampleProgram;
use App\Entity\FeaturedProgram;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use App\Repository\FeaturedRepository;
use App\Utils\APIHelper;
use App\Utils\ElapsedTimeStringFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OpenAPI\Server\Api\ProjectsApiInterface;
use OpenAPI\Server\Model\FeaturedProjectResponse;
use OpenAPI\Server\Model\ProjectResponse;
use OpenAPI\Server\Model\UploadErrorResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectsApi extends AbstractController implements ProjectsApiInterface
{
  private string $token;
  private ProgramManager $program_manager;
  private UserManager $user_manager;
  private SessionInterface $session;
  private ElapsedTimeStringFormatter $time_formatter;
  private RequestStack $request_stack;
  private TokenStorageInterface $token_storage;
  private EntityManagerInterface $entity_manager;
  private TranslatorInterface $translator;
  private UrlGeneratorInterface $url_generator;

  private FeaturedRepository $featured_repository;

  private ImageRepository $featured_image_repository;

  public function __construct(ProgramManager $program_manager, SessionInterface $session,
                              ElapsedTimeStringFormatter $time_formatter, FeaturedRepository $featured_repository,
                              ImageRepository $featured_image_repository, UserManager $user_manager,
                              RequestStack $request_stack, TokenStorageInterface $token_storage,
                              EntityManagerInterface $entity_manager, TranslatorInterface $translator,
                              UrlGeneratorInterface $url_generator)
  {
    $this->program_manager = $program_manager;
    $this->session = $session;
    $this->time_formatter = $time_formatter;
    $this->featured_repository = $featured_repository;
    $this->featured_image_repository = $featured_image_repository;
    $this->request_stack = $request_stack;
    $this->token_storage = $token_storage;
    $this->entity_manager = $entity_manager;
    $this->translator = $translator;
    $this->url_generator = $url_generator;
    $this->user_manager = $user_manager;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function setPandaAuth($value): void
  {
    $this->token = APIHelper::getPandaAuth($value);
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectIdGet(string $project_id, &$responseCode, array &$responseHeaders)
  {
    $projects = $this->program_manager->getProgram($project_id);
    if (null == $projects || empty($projects))
    {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;

    return $this->getProjectDataResponse($projects[0]);
  }

  /**
   * {@inheritdoc}
   */
  public function projectsFeaturedGet(string $platform = null, string $max_version = null, ?int $limit = 20, ?int $offset = 0, string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $max_version = APIHelper::setDefaultMaxVersionOnNull($max_version);
    $limit = APIHelper::setDefaultLimitOnNull($limit);
    $offset = APIHelper::setDefaultOffsetOnNull($offset);

    $programs = $this->featured_repository->getFeaturedPrograms($flavor, $limit, $offset, $platform, $max_version);

    $responseCode = Response::HTTP_OK;

    $featured_programs = [];

    /** @var FeaturedProgram $featured_program */
    foreach ($programs as &$featured_program)
    {
      $result = [
        'id' => $featured_program->getId(),
        'name' => $featured_program->getProgram()->getName(),
        'author' => $featured_program->getProgram()->getUser()->getUsername(),
        'featured_image' => $this->featured_image_repository->getAbsoluteWebPath($featured_program->getId(), $featured_program->getImageType(), true),
      ];
      $new_featured_project = new FeaturedProjectResponse($result);
      $featured_programs[] = $new_featured_project;
    }

    return $featured_programs;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsGet(string $category, ?string $accept_language = null, ?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $max_version = APIHelper::setDefaultMaxVersionOnNull($max_version);
    $limit = APIHelper::setDefaultLimitOnNull($limit);
    $offset = APIHelper::setDefaultOffsetOnNull($offset);
    $accept_language = APIHelper::setDefaultAcceptLanguageOnNull($accept_language);

    $programs = $this->program_manager->getProjects($category, $max_version, $limit, $offset, $flavor);
    $responseCode = Response::HTTP_OK;

    return $this->getProjectsDataResponse($programs);
  }

  /**
   * {@inheritdoc}
   */
  public function projectsPost(string $checksum, UploadedFile $file, ?string $accept_language = null, ?string $flavor = null, ?bool $private = false, &$responseCode, array &$responseHeaders)
  {
    $accept_language = APIHelper::setDefaultAcceptLanguageOnNull($accept_language);
    $private = $private ?? false;

    // File uploaded successful?
    if (!$file->isValid())
    {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY; // 422 => UploadError

      return new UploadErrorResponse(['error' => $this->translator->trans('api.projectsPost.upload_error', [], 'catroweb')]);
    }

    // Checking checksum
    $calculated_checksum = md5_file($file->getPathname());

    if (strtolower($calculated_checksum) != strtolower($checksum))
    {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY; // 422 => UploadError

      return new UploadErrorResponse(['error' => $this->translator->trans('api.projectsPost.invalid_checksum', [], 'catroweb')]);
    }

    // Getting the user who uploaded

    /** @var User $user */
    $user = $this->token_storage->getToken()->getUser();

    // Needed (for tests) to make sure everything is up to date (followers, ..)
    $this->entity_manager->refresh($user);

    // Adding the uploaded program
    $add_program_request = new AddProgramRequest($user, $file, $this->request_stack->getCurrentRequest()->getClientIp(), $accept_language, $flavor ? $flavor : 'pocketcode');

    try
    {
      $program = $this->program_manager->addProgram($add_program_request);
    }
    catch (Exception $e)
    {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY; // 422 => UploadError

      return new UploadErrorResponse(['error' => $this->translator->trans('api.projectsPost.creating_error', [], 'catroweb')]);
    }

    // Setting the program's attributes
    $program->setPrivate($private);
    $this->entity_manager->flush();

    // Since we have come this far, the project upload is completed
    $responseCode = Response::HTTP_CREATED; // 201 => Successful upload
    $responseHeaders['Location'] = $this->url_generator->generate(
      'program',
      [
        'id' => $program->getId(),
      ],
      UrlGenerator::ABSOLUTE_URL);

    return null;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsSearchGet(string $query_string, ?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $max_version = APIHelper::setDefaultMaxVersionOnNull($max_version);
    $limit = APIHelper::setDefaultLimitOnNull($limit);
    $offset = APIHelper::setDefaultOffsetOnNull($offset);

    $responseCode = Response::HTTP_OK;

    if ('' === $query_string || ctype_space($query_string))
    {
      return [];
    }

    $programs = $this->program_manager->search($query_string, $limit, $offset, $max_version, $flavor);

    return $this->getProjectsDataResponse($programs);
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsUserGet(?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $max_version = APIHelper::setDefaultMaxVersionOnNull($max_version);
    $limit = APIHelper::setDefaultLimitOnNull($limit);
    $offset = APIHelper::setDefaultOffsetOnNull($offset);

    $jwtPayload = $this->program_manager->decodeToken($this->token);
    if (!array_key_exists('username', $jwtPayload))
    {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $programs = $this->program_manager->getUserProjects($jwtPayload['username'], $limit, $offset, $flavor, $max_version);
    $responseCode = Response::HTTP_OK;

    return $this->getProjectsDataResponse($programs);
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsUserIdGet(string $user_id, ?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode, array &$responseHeaders)
  {
    $max_version = APIHelper::setDefaultMaxVersionOnNull($max_version);
    $limit = APIHelper::setDefaultLimitOnNull($limit);
    $offset = APIHelper::setDefaultOffsetOnNull($offset);

    if ('' === $user_id || ctype_space($user_id) || null == $this->user_manager->findOneBy(['id' => $user_id]))
    {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $programs = $this->program_manager->getUserPublicPrograms($user_id, $limit, $offset, $flavor, $max_version);
    $responseCode = Response::HTTP_OK;

    return $this->getProjectsDataResponse($programs);
  }

  /**
   * @param Program|ExampleProgram $program
   *
   * @throws Exception
   */
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
      'uploaded' => $project->getUploadedAt()->getTimestamp(),
      'uploaded_string' => $this->time_formatter->getElapsedTime($project->getUploadedAt()->getTimestamp()),
      'screenshot_large' => $this->program_manager->getScreenshotLarge($project->getId()),
      'screenshot_small' => $this->program_manager->getScreenshotSmall($project->getId()),
      'project_url' => ltrim($this->generateUrl(
        'program',
        [
          'flavor' => $this->session->get('flavor_context'),
          'id' => $project->getId(),
        ],
        UrlGeneratorInterface::ABSOLUTE_URL), '/'
      ),
      'download_url' => ltrim($this->generateUrl(
        'download',
        [
          'id' => $project->getId(),
        ],
        UrlGeneratorInterface::ABSOLUTE_URL), '/'),
      'filesize' => ($project->getFilesize() / 1_048_576),
    ]);
  }

  private function getProjectsDataResponse(array $projects): array
  {
    $projectsDataResponse = [];
    foreach ($projects as $project)
    {
      $projectData = $this->getProjectDataResponse($project);
      $projectsDataResponse[] = $projectData;
    }

    return $projectsDataResponse;
  }
}
