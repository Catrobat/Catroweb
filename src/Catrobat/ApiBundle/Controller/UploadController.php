<?php

namespace Catrobat\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\SecurityContext;
use Catrobat\CoreBundle\Model\UserManager;
use Catrobat\CoreBundle\Model\Requests\AddProgramRequest;
use Catrobat\CoreBundle\Model\ProgramManager;
use Catrobat\CoreBundle\Services\TokenGenerator;
use Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException;
use Buzz\Exception\InvalidArgumentException;

class UploadController
{
    protected $templating;
    protected $user_manager;
    protected $context;
    protected $program_manager;
    protected $tokenGenerator;
    
    public function __construct(EngineInterface $templating, UserManager $user_manager, SecurityContext $context, ProgramManager $program_manager, TokenGenerator $tokenGenerator)
    {
      $this->templating = $templating;
      $this->user_manager = $user_manager;
      $this->context = $context;
      $this->program_manager = $program_manager;
      $this->tokenGenerator = $tokenGenerator;
    }

    public function uploadAction(Request $request)
    {
      if ($request->files->count() != 1)
      {
        $response["statusCode"] = InvalidCatrobatFileException::MISSING_POST_DATA;
        $response["answer"] = "POST-Data not correct or missing!";
      }
      else if (!$request->request->has("fileChecksum"))
      {
        $response["statusCode"] = InvalidCatrobatFileException::MISSING_CHECKSUM;
        $response["answer"] = "Client did not send fileChecksum! Are you using an outdated version of Pocket Code?";
      }
      else if (md5_file($request->files->get(0)->getPathname()) != $request->request->get("fileChecksum"))
      {
        $response["statusCode"] = InvalidCatrobatFileException::INVALID_CHECKSUM;
        $response["answer"] = "invalid checksum";
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
          $response["statusCode"] = 200;
          $response["answer"] = "Your project was uploaded successfully!";
          $response["token"] = $user->getToken();
        }
        catch (InvalidCatrobatFileException $exception)
        {
          $response["statusCode"] = $exception->getStatusCode();
          switch ($exception->getStatusCode())
          {
            case InvalidCatrobatFileException::PROJECT_XML_MISSING:
              $response["answer"] = "unknown error: project_xml_not_found!";
              break;
            case InvalidCatrobatFileException::IMAGE_MISSING:
              $response["answer"] = "Project XML mentions a file which not exists in project-folder";
              break;
            case InvalidCatrobatFileException::UNEXPECTED_FILE:
              $response["answer"] = "unexpected file found";
              break;
            case InvalidCatrobatFileException::INVALID_XML:
              $response["answer"] = "invalid code xml";
              break;
            case InvalidCatrobatFileException::INVALID_FILE:
              $response["answer"] = "invalid file";
              break;
            default:
              $response["answer"] = "unknown error";
          }
        }
      }
        
//      $num_files = $this->context->getToken()->getUser()->getUsername(); //$request->request->get('fileChecksum'); //$request->files->count();
      $response["preHeaderMessages"] = "";
      return $this->templating->renderResponse('CatrobatApiBundle:Api:upload.json.twig', array("response" => $response));
    }
}
