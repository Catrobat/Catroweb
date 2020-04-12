<?php

namespace App\Api;

use App\Catrobat\Requests\AddProgramRequest;
use App\Catrobat\Services\FeaturedImageRepository;
use App\Catrobat\Services\Formatter\ElapsedTimeStringFormatter;
use App\Entity\FeaturedProgram;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Repository\FeaturedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OpenAPI\Server\Api\ProjectsApiInterface;
use OpenAPI\Server\Model\FeaturedProject;
use OpenAPI\Server\Model\Project;
use OpenAPI\Server\Model\UploadError;
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
  private SessionInterface $session;
  private ElapsedTimeStringFormatter $time_formatter;
  private RequestStack $request_stack;
  private TokenStorageInterface $token_storage;
  private EntityManagerInterface $entity_manager;
  private TranslatorInterface $translator;
  private UrlGeneratorInterface $url_generator;

  private FeaturedRepository $featured_repository;

  private FeaturedImageRepository $featured_image_repository;

  public function __construct(ProgramManager $program_manager, SessionInterface $session,
                              ElapsedTimeStringFormatter $time_formatter, FeaturedRepository $featured_repository,
                              FeaturedImageRepository $featured_image_repository,
                              RequestStack $request_stack,
                              TokenStorageInterface $token_storage,
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
  public function projectsPost(string $checksum, UploadedFile $file, ?string $accept_language = null, ?string $flavor = null, ?bool $private = false, &$responseCode, array &$responseHeaders)
  {
    // File uploaded successful?
    if (!$file->isValid())
    {
      $responseCode = 422; // 422 => UploadError
      return new UploadError(['error' => $this->translator->trans('api.projectsPost.upload_error', [], 'catroweb')]);
    }

    // Checking checksum
    $calculated_checksum = md5_file($file->getPathname());

    if (strtolower($calculated_checksum) != strtolower($checksum))
    {
      $responseCode = 422; // 422 => UploadError
      return new UploadError(['error' => $this->translator->trans('api.projectsPost.invalid_checksum', [], 'catroweb')]);
    }

    // Getting the user who uploaded

    /** @var User $user */
    $user = $this->token_storage->getToken()->getUser();

    // Needed (for tests) to make sure everything is up to date (followers, ..)
    $this->entity_manager->refresh($user);

    // Adding the uploaded program
    $add_program_request = new AddProgramRequest($user, $file,$this->request_stack->getCurrentRequest()->getClientIp(),
      null, $accept_language, $flavor ? $flavor : 'pocketcode');

    try
    {
      $program = $this->program_manager->addProgram($add_program_request);
    }
    catch (Exception $e)
    {
      $responseCode = 422; // 422 => UploadError
      return new UploadError(['error' => $this->translator->trans('api.projectsPost.creating_error', [], 'catroweb')]);
    }

    // Setting the program's attributes
    if (null !== $private)
    {
      $program->setPrivate($private);
      $this->entity_manager->flush();
    }

    // Since we have come this far, the project upload is completed
    $responseCode = 201; // 201 => Successful upload
    $responseHeaders['Location'] = $this->url_generator->generate(
      'program',
      [
        'id' => $program->getId(),
      ],
      UrlGenerator::ABSOLUTE_URL);
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
        'filesize' => ($program->getFilesize() / 1_048_576),
      ];
      $project = new Project($result);
      $projects[] = $project;
    }

    return $projects;
  }
}
