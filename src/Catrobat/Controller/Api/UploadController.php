<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\Exceptions\Upload\InvalidChecksumException;
use App\Catrobat\Exceptions\Upload\MissingChecksumException;
use App\Catrobat\Exceptions\Upload\MissingPostDataException;
use App\Catrobat\Exceptions\Upload\NoGameJamException;
use App\Catrobat\Requests\AddProgramRequest;
use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\Services\TokenGenerator;
use App\Catrobat\StatusCode;
use App\Entity\GameJam;
use App\Entity\NewProgramNotification;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use App\Repository\GameJamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UploadController.
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
   * @var GameJamRepository
   */
  private $gamejamrepository;

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
   * @var EntityManagerInterface
   */
  private $em;

  /**
   * UploadController constructor.
   */
  public function __construct(UserManager $usermanager, TokenStorageInterface $tokenstorage, ProgramManager $programmanager,
                              GameJamRepository $gamejamrepository, TokenGenerator $tokengenerator,
                              TranslatorInterface $translator, LoggerInterface $logger,
                              CatroNotificationService $catroNotificationService, EntityManagerInterface $em)
  {
    $this->usermanager = $usermanager;
    $this->tokenstorage = $tokenstorage;
    $this->programmanager = $programmanager;
    $this->gamejamrepository = $gamejamrepository;
    $this->tokengenerator = $tokengenerator;
    $this->translator = $translator;
    $this->logger = $logger;
    $this->catroNotificationService = $catroNotificationService;
    $this->em = $em;
  }

  /**
   * @Route("/api/upload/upload.json", name="catrobat_api_upload", defaults={"_format": "json"}, methods={"POST"})
   *
   * @throws \Exception
   *
   * @return JsonResponse
   */
  public function uploadAction(Request $request)
  {
    $this->logger->info('Uploading a project...');

    return $this->processUpload($request);
  }

  /**
   * @Route("/api/gamejam/submit.json", name="catrobat_api_gamejam_submit", defaults={"_format": "json"},
   * methods={"POST"})
   *
   * @throws \Doctrine\ORM\NonUniqueResultException
   * @throws \Exception
   *
   * @return JsonResponse
   */
  public function submitAction(Request $request)
  {
    $jam = $this->gamejamrepository->getCurrentGameJam();
    if (null === $jam)
    {
      throw new NoGameJamException();
    }

    return $this->processUpload($request, $jam);
  }

  /**
   * @param null $gamejam
   *
   * @throws \Exception
   *
   * @return JsonResponse
   */
  private function processUpload(Request $request, $gamejam = null)
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

    $user = $this->tokenstorage->getToken()->getUser();

    // Needed (for tests) to make sure everything is up to date (followers, ..)
    $this->em->refresh($user);
    // ---

    if ($request->request->has('flavor'))
    {
      $flavor = $request->request->get('flavor');
    }

    $add_program_request = new AddProgramRequest($user, $file, $request->getClientIp(),
      $gamejam, $request->request->get('deviceLanguage'), $flavor);

    $program = $this->programmanager->addProgram($add_program_request);
    if (null === $program)
    {
      $response = $this->createUploadFailedResponse($request, $gamejam, $user);
    }
    else
    {
      foreach ($user->getFollowers() as $follower)
      {
        $notification = new NewProgramNotification($follower, $program);
        $this->catroNotificationService->addNotification($notification);
      }
      $response = $this->createUploadResponse($request, $gamejam, $user, $program);
    }
    $this->logger->info('Uploading a project done : '.json_encode($response));

    return JsonResponse::create($response);
  }

  /**
   * @param $gamejam GameJam
   * @param $user    User
   * @param $program Program
   * @param $request Request
   *
   * @return mixed
   */
  private function assembleFormUrl($gamejam, $user, $program, $request)
  {
    /**
     * @var GameJam
     */
    $languageCode = $this->getLanguageCode($request);

    $url = $gamejam->getFormUrl();
    $url = str_replace('%CAT_ID%', $program->getId(), $url);
    $url = str_replace('%CAT_MAIL%', $user->getEmail(), $url);
    $url = str_replace('%CAT_NAME%', $user->getUsername(), $url);

    return str_replace('%CAT_LANGUAGE%', $languageCode, $url);
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
   * @param $request Request
   *
   * @return string
   */
  private function getLanguageCode(Request $request)
  {
    $languageCode = strtoupper(substr($request->getLocale(), 0, 2));
    if ('DE' !== $languageCode)
    {
      $languageCode = 'EN';
    }

    return $languageCode;
  }

  /**
   * @param $request  Request
   * @param $gamejam  GameJam
   * @param $user     User
   * @param $program  Program
   *
   * @return mixed
   */
  private function createUploadResponse(Request $request, $gamejam, $user, $program)
  {
    /**
     * @var User
     * @var Program $program
     */
    $response = [];
    $this->usermanager->updateUser($user);

    $response['projectId'] = $program->getId();
    $response['statusCode'] = StatusCode::OK;
    $response['answer'] = $this->trans('success.upload');
    $response['token'] = $user->getUploadToken();
    if (null !== $gamejam && !$program->isAcceptedForGameJam())
    {
      $response['form'] = $this->assembleFormUrl($gamejam, $user, $program, $request);
    }

    $request->attributes->set('program_id', $program->getId());
    $response['preHeaderMessages'] = '';

    return $response;
  }

  /**
   * @param  $request Request
   * @param  $user User
   * @param  $gamejam GameJam
   *
   * @return array
   */
  private function createUploadFailedResponse($request, $gamejam, $user)
  {
    /**
     * @var User
     */
    $response = [];
    $this->usermanager->updateUser($user);

    $response['projectId'] = 0;
    $response['statusCode'] = StatusCode::FILE_UPLOAD_FAILED;
    $response['answer'] = $this->trans('failure.upload');
    $response['token'] = $user->getUploadToken();

    $request->attributes->set('program_id', 0);
    $response['preHeaderMessages'] = '';

    return $response;
  }
}
