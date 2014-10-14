<?php

namespace AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\SecurityContext;
use Catrobat\CoreBundle\Model\UserManager;
use Catrobat\CoreBundle\Model\Requests\AddProgramRequest;
use Catrobat\CoreBundle\Model\ProgramManager;
use Catrobat\CoreBundle\Services\TokenGenerator;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\StatusCode;
use Symfony\Component\Translation\Translator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class UploadController extends Controller
{
  
  /**
   * @Route("/api/upload/upload.json", name="catrobat_api_upload", defaults={"_format": "json"})
   * @Method({"POST"})
   */
  public function uploadAction(Request $request)
  {
    $user_manager = $this->get("usermanager");
    $context = $this->get("security.context");
    $program_manager = $this->get("programmanager");
    $tokenGenerator = $this->get("tokengenerator");
    
    $response = array();
    if ($request->files->count() != 1)
    {
      $response["statusCode"] = StatusCode::MISSING_POST_DATA;
      $response["answer"] = $this->trans("error.post-data");
    }
    else if (!$request->request->has("fileChecksum"))
    {
      $response["statusCode"] = StatusCode::MISSING_CHECKSUM;
      $response["answer"] = $this->trans("error.checksum.missing");
    }
    else
    {
      $file = array_values($request->files->all())[0];
      if (md5_file($file->getPathname()) != $request->request->get("fileChecksum"))
      {
        $response["statusCode"] = StatusCode::INVALID_CHECKSUM;
        $response["answer"] = $this->trans("error.checksum.invalid");
      }
      else
      {
        try
        {
          $add_program_request = new AddProgramRequest($context->getToken()->getUser(), $file);
  
          $id = $program_manager->addProgram($add_program_request)->getId();
          $user = $context->getToken()->getUser();
          $user->setToken($tokenGenerator->generateToken());
          $user_manager->updateUser($user);
  
          $response["projectId"] = $id;
          $response["statusCode"] = StatusCode::OK;
          $response["answer"] = $this->trans("success.upload");
          $response["token"] = $user->getToken();
        }
        catch (InvalidCatrobatFileException $exception)
        {
          $response["statusCode"] = $exception->getStatusCode();
          switch ($exception->getStatusCode())
          {
            case StatusCode::PROJECT_XML_MISSING:
              $response["answer"] = $this->trans("error.xml.missing");
              break;
            case StatusCode::INVALID_XML:
              $response["answer"] = $this->trans("error.xml.invalid");
              break;
            case StatusCode::IMAGE_MISSING:
              $response["answer"] = $this->trans("error.image.missing");
              break;
            case StatusCode::UNEXPECTED_FILE:
              $response["answer"] = $this->trans("error.file.unexpected");
              break;
            case StatusCode::INVALID_FILE:
              $response["answer"] = $this->trans("error.file.invalid");
              break;
            case StatusCode::RUDE_WORD_IN_DESCRIPTION:
              $response["answer"] = $this->trans("error.description.rude");
              break;
            case StatusCode::RUDE_WORD_IN_PROGRAM_NAME:
              $response["answer"] = $this->trans("error.programname.rude");
              break;
            default:
              $response["answer"] = $this->trans("error.file.unknown");
          }
        }
      }
    }

    $response["preHeaderMessages"] = "";
    return JsonResponse::create($response);
  }

  private function trans($message, $parameters = array())
  {
    return  $this->get("translator")->trans($message,$parameters,"catroweb_api");
  }
}
