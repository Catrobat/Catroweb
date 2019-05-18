<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\Services\CatroNotificationService;
use App\Entity\Program;
use App\Entity\NewProgramNotification;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\UserManager;
use App\Catrobat\Requests\AddProgramRequest;
use App\Entity\ProgramManager;
use App\Catrobat\Services\TokenGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Catrobat\StatusCode;
use Symfony\Component\Routing\Annotation\Route;
use App\Catrobat\Exceptions\Upload\MissingChecksumException;
use App\Catrobat\Exceptions\Upload\InvalidChecksumException;
use App\Catrobat\Exceptions\Upload\MissingPostDataException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class UploadController
 * @package App\Catrobat\Controller\Api
 */
class UploadController
{

  /**
   * @var UserManager
   */
  private $usermanager;

  /**
   * @var TokenStorage
   */
  private $tokenstorage;

  /**
   * @var ProgramManager
   */
  private $programmanager;

  /**
   * @var TokenGenerator
   */
  private $tokengenerator;

  /**
   * @var TranslatorInterface
   */
  private $translator;

  /**
   * @var CatroNotificationService
   */
  private $catroNotificationService;

  /**
   * @var LoggerInterface
   */
  private $logger;

  /**
   * UploadController constructor.
   *
   * @param UserManager         $usermanager
   * @param TokenStorage        $tokenstorage
   * @param ProgramManager      $programmanager
   * @param TokenGenerator      $tokengenerator
   * @param TranslatorInterface $translator
   * @param LoggerInterface     $logger
   * @param CatroNotificationService $catroNotificationService
   */

  public function __construct(UserManager $usermanager, TokenStorage $tokenstorage, ProgramManager $programmanager,
                              TokenGenerator $tokengenerator, TranslatorInterface $translator, LoggerInterface $logger,
                              CatroNotificationService $catroNotificationService)
  {
    $this->usermanager = $usermanager;
    $this->tokenstorage = $tokenstorage;
    $this->programmanager = $programmanager;
    $this->tokengenerator = $tokengenerator;
    $this->translator = $translator;
    $this->logger = $logger;
    $this->catroNotificationService = $catroNotificationService;
  }


  /**
   * @Route("/api/upload/upload.json", name="catrobat_api_upload", defaults={"_format": "json"}, methods={"POST"})
   *
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Exception
   */
  public function uploadAction(Request $request)
  {
    return $this->processUpload($request);
  }


  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Exception
   */
  private function processUpload(Request $request)
  {
    /**
     * @var $file File
     * @var $user User
     */

    if ($request->files->count() !== 1)
    {
      $this->logger->error("Missing POST data");
      throw new MissingPostDataException();
    }
    elseif (!$request->request->has('fileChecksum'))
    {
      $this->logger->error("Missing Checksum");
      throw new MissingChecksumException();
    }

    $file = array_values($request->files->all())[0];
    if (md5_file($file->getPathname()) !== $request->request->get('fileChecksum'))
    {
      $this->logger->error("UploadError " . StatusCode::INVALID_CHECKSUM, [
        "checksum_symfony" => md5($file->getPathname()),
        "checksum_app" => $request->request->get('fileChecksum'),
      ]);
      throw new InvalidChecksumException();
    }

    $user = $this->tokenstorage->getToken()->getUser();

    $flavor = 'pocketcode';

    if ($request->request->has('flavor'))
    {
      $flavor = $request->request->get('flavor');
    }

    $add_program_request = new AddProgramRequest(
      $user,
      $file,
      $request->getClientIp(),
      $request->request->get('deviceLanguage'),
      $flavor
    );

    $program = $this->programmanager->addProgram($add_program_request);
    if ($program === null)
    {
      $response = $this->createUploadFailedResponse($request, $user);
    }
    else
    {
      foreach ($user->getFollowers() as $follower)
      {
        $notification = new NewProgramNotification($follower, $program);
        $this->catroNotificationService->addNotification($notification);
      }
      $response = $this->createUploadResponse($request, $user, $program);
    }
    return JsonResponse::create($response);
  }



  /**
   * @param       $message
   * @param array $parameters
   *
   * @return string
   */
  private function trans($message, $parameters = [])
  {
    return $this->translator->trans($message, $parameters, 'catroweb');
  }


  /**
   * @param $request  Request
   * @param $user     User
   * @param $program  Program
   *
   * @return mixed
   */
  private function createUploadResponse(Request $request, $user, $program)
  {
    /**
     * @var $user    User
     * @var $program Program
     */
    $response = [];
    $user->setUploadToken($this->tokengenerator->generateToken());
    $this->usermanager->updateUser($user);

    $response['projectId'] = $program->getId();
    $response['statusCode'] = StatusCode::OK;
    $response['answer'] = $this->trans('success.upload');
    $response['token'] = $user->getUploadToken();

    $request->attributes->set('post_to_facebook', false);
    $request->attributes->set('program_id', $program->getId());
    $response['preHeaderMessages'] = '';

    return $response;
  }


  /**
   * @param  $request Request
   * @param  $user User
   *
   * @return array
   */
  private function createUploadFailedResponse($request, $user)
  {
    /**
     * @var $user User
     */
    $response = [];
    $user->setUploadToken($this->tokengenerator->generateToken());
    $this->usermanager->updateUser($user);

    $response['projectId'] = 0;
    $response['statusCode'] = StatusCode::FILE_UPLOAD_FAILED;
    $response['answer'] = $this->trans('failure.upload');
    $response['token'] = $user->getUploadToken();

    $request->attributes->set('post_to_facebook', false);
    $request->attributes->set('program_id', 0);
    $response['preHeaderMessages'] = '';

    return $response;
  }
}
