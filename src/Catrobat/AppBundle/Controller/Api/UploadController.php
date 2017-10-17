<?php

namespace Catrobat\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Requests\AddProgramRequest;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Services\TokenGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\AppBundle\StatusCode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Catrobat\AppBundle\Exceptions\Upload\MissingChecksumException;
use Catrobat\AppBundle\Exceptions\Upload\InvalidChecksumException;
use Catrobat\AppBundle\Exceptions\Upload\MissingPostDataException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\TranslatorInterface;
use Catrobat\AppBundle\Entity\GameJamRepository;
use Catrobat\AppBundle\Exceptions\Upload\NoGameJamException;

/**
 * @Route(service="controller.upload")
 */
class UploadController
{
  private $usermanager;

  private $tokenstorage;

  private $programmanager;

  private $gamejamrepository;

  private $tokengenerator;

  private $translator;

  public function __construct(UserManager $usermanager, TokenStorage $tokenstorage, ProgramManager $programmanager, GameJamRepository $gamejamrepository, TokenGenerator $tokengenerator, TranslatorInterface $translator)
  {
    $this->usermanager = $usermanager;
    $this->tokenstorage = $tokenstorage;
    $this->programmanager = $programmanager;
    $this->gamejamrepository = $gamejamrepository;
    $this->tokengenerator = $tokengenerator;
    $this->translator = $translator;
  }

  /**
   * @Route("/api/upload/upload.json", name="catrobat_api_upload", defaults={"_format": "json"})
   * @Method({"POST"})
   */
  public function uploadAction(Request $request)
  {
    return $this->processUpload($request);
  }

  /**
   * @Route("/api/gamejam/submit.json", name="catrobat_api_gamejam_submit", defaults={"_format": "json"})
   * @Method({"POST"})
   */
  public function submitAction(Request $request)
  {
    $jam = $this->gamejamrepository->getCurrentGameJam();
    if ($jam == null)
    {
      throw new NoGameJamException();
    }

    return $this->processUpload($request, $jam);
  }

  private function processUpload(Request $request, $gamejam = null)
  {
    if ($request->files->count() != 1)
    {
      throw new MissingPostDataException();
    }
    elseif (!$request->request->has('fileChecksum'))
    {
      throw new MissingChecksumException();
    }

    $file = array_values($request->files->all())[0];
    if (md5_file($file->getPathname()) != $request->request->get('fileChecksum'))
    {
      throw new InvalidChecksumException();
    }

    $user = $this->tokenstorage->getToken()->getUser();

    $flavor = 'pocketcode';
    if ($user->getNolbUser()) {
        $flavor = 'create@school';
    }
    if ($request->request->has('flavor')) {
      $flavor = $request->request->get('flavor');
    }


    $add_program_request = new AddProgramRequest($user, $file, $request->getClientIp(), $gamejam, $request->request->get('deviceLanguage'), $flavor);

    $program = $this->programmanager->addProgram($add_program_request);
    if ($program == null)
    {
      $response = $this->createUploadFailedResponse($request, $gamejam, $user);
    }
    else
    {
      $response = $this->createUploadResponse($request, $gamejam, $user, $program);
    }

    return JsonResponse::create($response);
  }

  private function assembleFormUrl($gamejam, $user, $program, $request)
  {
    $languageCode = $this->getLanguageCode($request);

    $url = $gamejam->getFormUrl();
    $url = str_replace("%CAT_ID%", $program->getId(), $url);
    $url = str_replace("%CAT_MAIL%", $user->getEmail(), $url);
    $url = str_replace("%CAT_NAME%", $user->getUsername(), $url);
    $url = str_replace("%CAT_LANGUAGE%", $languageCode, $url);

    return $url;
  }

  private function trans($message, $parameters = [])
  {
    return $this->translator->trans($message, $parameters, 'catroweb');
  }

  private function getLanguageCode($request)
  {
    $languageCode = strtoupper(substr($request->getLocale(), 0, 2));

    if ($languageCode != "DE")
    {
      $languageCode = "EN";
    }

    return $languageCode;
  }

  /**
   * @param Request $request
   * @param $gamejam
   * @param $user
   * @param $program
   * @param $response
   *
   * @return mixed
   */
  private function createUploadResponse(Request $request, $gamejam, $user, $program)
  {
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

    $request->attributes->set('post_to_facebook', true);
    $request->attributes->set('program_id', $program->getId());
    $response['preHeaderMessages'] = '';

    return $response;
  }

  private function createUploadFailedResponse($request, $gamejam, $user)
  {
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
