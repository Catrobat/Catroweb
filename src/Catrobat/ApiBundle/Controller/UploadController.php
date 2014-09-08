<?php

namespace Catrobat\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\SecurityContext;
use Catrobat\CoreBundle\Model\UserManager;
use Catrobat\CoreBundle\Model\Requests\AddProgramRequest;
use Catrobat\CoreBundle\Model\ProgramManager;
use Catrobat\CoreBundle\Services\TokenGenerator;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\CoreBundle\StatusCode;
use Symfony\Component\Translation\Translator;

class UploadController
{
  protected $user_manager;
  protected $context;
  protected $program_manager;
  protected $tokenGenerator;
  protected $translator;

  public function __construct(UserManager $user_manager, SecurityContext $context, ProgramManager $program_manager, TokenGenerator $tokenGenerator, Translator $translator)
  {
    $this->user_manager = $user_manager;
    $this->context = $context;
    $this->program_manager = $program_manager;
    $this->tokenGenerator = $tokenGenerator;
    $this->translator = $translator;
  }

  public function uploadAction(Request $request)
  {
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
    else if (md5_file($request->files->get(0)->getPathname()) != $request->request->get("fileChecksum"))
    {
      $response["statusCode"] = StatusCode::INVALID_CHECKSUM;
      $response["answer"] = $this->trans("error.checksum.invalid");
    }
    else
    {
      try
      {
        $add_program_request = new AddProgramRequest($this->context->getToken()->getUser(), $request->files->get(0));

        $id = $this->program_manager->addProgram($add_program_request)->getId();
        $user = $this->context->getToken()->getUser();
        $user->setToken($this->tokenGenerator->generateToken());
        $this->user_manager->updateUser($user);

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
          default:
            $response["answer"] = $this->trans("error.file.unknown");
        }
      }
    }

    $response["preHeaderMessages"] = "";
    return JsonResponse::create($response);
  }

  private function trans($message, $parameters = array())
  {
    return $this->translator->trans($message,$parameters,"catroweb_api");
  }
}
