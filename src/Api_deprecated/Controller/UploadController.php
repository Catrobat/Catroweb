<?php

namespace App\Api_deprecated\Controller;

use App\Catrobat\Exceptions\Upload\InvalidChecksumException;
use App\Catrobat\Exceptions\Upload\MissingChecksumException;
use App\Catrobat\Exceptions\Upload\MissingPostDataException;
use App\Catrobat\Requests\AddProgramRequest;
use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\StatusCode;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated
 */
class UploadController
{
  private UserManager $user_manager;

  private TokenStorageInterface $token_storage;

  private ProgramManager $program_manager;

  private TranslatorInterface $translator;

  private CatroNotificationService $catro_notification_service;

  private LoggerInterface $logger;

  private EntityManagerInterface $em;

  public function __construct(UserManager $user_manager, TokenStorageInterface $token_storage,
                              ProgramManager $program_manager,
                              TranslatorInterface $translator, LoggerInterface $logger,
                              CatroNotificationService $catro_notification_service, EntityManagerInterface $em)
  {
    $this->user_manager = $user_manager;
    $this->token_storage = $token_storage;
    $this->program_manager = $program_manager;
    $this->translator = $translator;
    $this->logger = $logger;
    $this->catro_notification_service = $catro_notification_service;
    $this->em = $em;
  }

  /**
   * @deprecated
   *
   * @Route("/api/upload/upload.json", name="catrobat_api_upload", defaults={"_format": "json"}, methods={"POST"})
   *
   * @throws Exception
   */
  public function uploadAction(Request $request): JsonResponse
  {
    $this->logger->info('Uploading a project...');

    return $this->processUpload($request);
  }

  /**
   * @throws Exception
   */
  private function processUpload(Request $request): JsonResponse
  {
    /* @var $file File */
    /* @var $user User */

    if (1 !== $request->files->count())
    {
      $this->logger->error('Missing POST data');
      throw new MissingPostDataException();
    }
    if (!$request->request->has('fileChecksum'))
    {
      $this->logger->error('Missing Checksum');
      throw new MissingChecksumException();
    }

    $file = array_values($request->files->all())[0];
    if (md5_file($file->getPathname()) !== $request->request->get('fileChecksum'))
    {
      $this->logger->error('UploadError '.StatusCode::INVALID_CHECKSUM, [
        'checksum_symfony' => md5($file->getPathname()),
        'checksum_app' => $request->request->get('fileChecksum'),
      ]);
      throw new InvalidChecksumException();
    }

    $flavor = 'pocketcode';

    /** @var User $user */
    $user = $this->token_storage->getToken()->getUser();

    // Needed (for tests) to make sure everything is up to date (followers, ..)
    $this->em->refresh($user);
    // ---

    if ($request->request->has('flavor'))
    {
      $flavor = $request->request->get('flavor');
    }

    $add_program_request = new AddProgramRequest($user, $file, $request->getClientIp(),
      $request->request->get('deviceLanguage'), $flavor);

    $program = $this->program_manager->addProgram($add_program_request);
    if (null === $program)
    {
      $response = $this->createUploadFailedResponse($request, $user);
    }
    else
    {
      $response = $this->createUploadResponse($request, $user, $program);
    }
    $this->logger->info('Uploading a project done : '.json_encode($response, JSON_THROW_ON_ERROR));

    return JsonResponse::create($response);
  }

  private function trans(string $message, array $parameters = []): string
  {
    return $this->translator->trans($message, $parameters, 'catroweb');
  }

  private function getLanguageCode(Request $request): string
  {
    $languageCode = strtoupper(substr($request->getLocale(), 0, 2));
    if ('DE' !== $languageCode)
    {
      $languageCode = 'EN';
    }

    return $languageCode;
  }

  private function createUploadResponse(Request $request, User $user, Program $program): array
  {
    $response = [];
    $this->user_manager->updateUser($user);

    $response['projectId'] = $program->getId();
    $response['statusCode'] = Response::HTTP_OK;
    $response['answer'] = $this->trans('success.upload');
    $response['token'] = $user->getUploadToken();
    $request->attributes->set('program_id', $program->getId());
    $response['preHeaderMessages'] = '';

    return $response;
  }

  private function createUploadFailedResponse(Request $request, User $user): array
  {
    $response = [];
    $this->user_manager->updateUser($user);

    $response['projectId'] = 0;
    $response['statusCode'] = StatusCode::FILE_UPLOAD_FAILED;
    $response['answer'] = $this->trans('failure.upload');
    $response['token'] = $user->getUploadToken();

    $request->attributes->set('program_id', 0);
    $response['preHeaderMessages'] = '';

    return $response;
  }
}
