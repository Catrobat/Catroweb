<?php

declare(strict_types=1);

namespace App\Api_deprecated\Controller;

use App\Admin\System\FeatureFlag\FeatureFlagManager;
use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\Project\AddProjectRequest;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\ProjectManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated
 */
class UploadController extends AbstractController
{
  public function __construct(
    private readonly UserManager $user_manager,
    private readonly TokenStorageInterface $token_storage,
    private readonly ProjectManager $project_manager,
    private readonly TranslatorInterface $translator,
    private readonly LoggerInterface $logger,
    private readonly EntityManagerInterface $em,
    private readonly FeatureFlagManager $feature_flag_manager)
  {
  }

  /**
   * @throws \Exception|ORMException
   *
   * @deprecated
   */
  #[Route(path: '/api/upload/upload.json', name: 'catrobat_api_upload', defaults: ['_format' => 'json'], methods: ['POST'])]
  public function uploadAction(Request $request): JsonResponse
  {
    $this->logger->warning('Deprecated upload route is used!');

    return $this->processUpload($request);
  }

  /**
   * @throws ORMException
   * @throws \JsonException
   */
  private function processUpload(Request $request): JsonResponse
  {
    /** @var User|null $user */
    $user = $this->token_storage->getToken()->getUser();

    if (!$user?->isVerified() && $this->forceUserVerification()) {
      return new JsonResponse(null, Response::HTTP_FORBIDDEN);
    }

    /* @var $file File */
    /* @var $user User */

    if (1 !== $request->files->count()) {
      $this->logger->error('Missing POST data');
      throw new InvalidCatrobatFileException('errors.post-data', 501);
    }

    if (!$request->request->has('fileChecksum')) {
      $this->logger->error('Missing Checksum');
      throw new InvalidCatrobatFileException('errors.checksum.missing', 503);
    }

    $file = array_values($request->files->all())[0];
    if (empty($file) || empty($file->getPathname())) {
      throw new InvalidCatrobatFileException('errors.checksum.invalid', 501);
    }

    if (md5_file($file->getPathname()) !== $request->request->get('fileChecksum')) {
      $this->logger->error('UploadError checksum', [
        'checksum_symfony' => md5((string) $file->getPathname()),
        'checksum_app' => $request->request->get('fileChecksum'),
      ]);
      throw new InvalidCatrobatFileException('errors.checksum.invalid', 504);
    }

    $flavor = Flavor::POCKETCODE;

    // Needed to make sure everything is up to date (followers, ..)
    $this->em->refresh($user);
    // ---

    if ($request->request->has('flavor')) {
      $flavor = $request->request->get('flavor');
      $flavor = is_null($flavor) ? $flavor : (string) $flavor;
    }

    $language = $request->request->get('deviceLanguage');
    $language = is_null($language) ? $language : (string) $language;

    $add_project_request = new AddProjectRequest($user, $file, $request->getClientIp(), $language, $flavor);

    $project = $this->project_manager->addProject($add_project_request);
    if (!$project instanceof Program) {
      $response = $this->createUploadFailedResponse($request, $user);
    } else {
      $response = $this->createUploadResponse($request, $user, $project);
    }

    $this->logger->info('Uploading a project done : '.json_encode($response, JSON_THROW_ON_ERROR));

    return new JsonResponse($response);
  }

  private function trans(string $message, array $parameters = []): string
  {
    return $this->translator->trans($message, $parameters, 'catroweb');
  }

  private function createUploadResponse(Request $request, User $user, Program $project): array
  {
    $response = [];
    $this->user_manager->updateUser($user);

    $response['projectId'] = $project->getId();
    $response['statusCode'] = Response::HTTP_OK;
    $response['answer'] = $this->trans('success.upload');
    $response['token'] = $user->getUploadToken();
    $request->attributes->set('program_id', $project->getId());
    $response['preHeaderMessages'] = '';

    return $response;
  }

  private function createUploadFailedResponse(Request $request, User $user): array
  {
    $response = [];
    $this->user_manager->updateUser($user);

    $response['projectId'] = 0;
    $response['statusCode'] = 521;
    $response['answer'] = $this->trans('failure.upload');
    $response['token'] = $user->getUploadToken();

    $request->attributes->set('program_id', 0);
    $response['preHeaderMessages'] = '';

    return $response;
  }

  private function forceUserVerification(): bool
  {
    return $this->feature_flag_manager->isEnabled('force-account-verification');
  }
}
