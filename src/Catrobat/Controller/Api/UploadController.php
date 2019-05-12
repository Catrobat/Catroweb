<?php

namespace App\Catrobat\Controller\Api;

use App\Entity\GameJam;
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
use App\Repository\GameJamRepository;
use App\Catrobat\Exceptions\Upload\NoGameJamException;

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
   * @var LoggerInterface
   */
  private $logger;

  /**
   * UploadController constructor.
   *
   * @param UserManager         $usermanager
   * @param TokenStorage        $tokenstorage
   * @param ProgramManager      $programmanager
   * @param GameJamRepository   $gamejamrepository
   * @param TokenGenerator      $tokengenerator
   * @param TranslatorInterface $translator
   * @param LoggerInterface     $logger
   */

  public function __construct(UserManager $usermanager, TokenStorage $tokenstorage, ProgramManager $programmanager,
                              GameJamRepository $gamejamrepository, TokenGenerator $tokengenerator,
                              TranslatorInterface $translator, LoggerInterface $logger)
  {
    $this->usermanager = $usermanager;
    $this->tokenstorage = $tokenstorage;
    $this->programmanager = $programmanager;
    $this->gamejamrepository = $gamejamrepository;
    $this->tokengenerator = $tokengenerator;
    $this->translator = $translator;
    $this->logger = $logger;
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
   * @Route("/api/gamejam/submit.json", name="catrobat_api_gamejam_submit", defaults={"_format": "json"},
   *   methods={"POST"})
   *
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Doctrine\ORM\NonUniqueResultException
   * @throws \Exception
   */
  public function submitAction(Request $request)
  {
    $jam = $this->gamejamrepository->getCurrentGameJam();
    if ($jam === null)
    {
      throw new NoGameJamException();
    }

    return $this->processUpload($request, $jam);
  }

  /**
   * @param Request $request
   * @param null    $gamejam
   *
   * @return JsonResponse
   * @throws \Exception
   */
  private function processUpload(Request $request, $gamejam = null)
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

    $add_program_request = new AddProgramRequest($user, $file, $request->getClientIp(),
      $gamejam, $request->request->get('deviceLanguage'), $flavor);

    $program = $this->programmanager->addProgram($add_program_request);
    if ($program === null)
    {
      $response = $this->createUploadFailedResponse($request, $gamejam, $user);
    }
    else
    {
      foreach ($user->getFollowers() as $follower)
      {
        $notification_service = $request->get("catro_notification_service");
        $notification = new NewProgramNotification($follower, $program);
        $notification_service->addNotification($notification);
      }
      $response = $this->createUploadResponse($request, $gamejam, $user, $program);
    }
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
     * @var $gamejam GameJam
     */
    $languageCode = $this->getLanguageCode($request);

    $url = $gamejam->getFormUrl();
    $url = str_replace("%CAT_ID%", $program->getId(), $url);
    $url = str_replace("%CAT_MAIL%", $user->getEmail(), $url);
    $url = str_replace("%CAT_NAME%", $user->getUsername(), $url);
    $url = str_replace("%CAT_LANGUAGE%", $languageCode, $url);

    return $url;
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
    if ($languageCode !== "DE")
    {
      $languageCode = "EN";
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
    if ($gamejam !== null && !$program->isAcceptedForGameJam())
    {
      $response['form'] = $this->assembleFormUrl($gamejam, $user, $program, $request);
    }

    $request->attributes->set('post_to_facebook', false);
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
