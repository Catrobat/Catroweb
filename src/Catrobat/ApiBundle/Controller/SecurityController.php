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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Catrobat\ApiBundle\Requests\CreateUserRequest;
use Symfony\Component\Translation\Translator;

class SecurityController
{
    protected $user_manager;
    protected $tokenGenerator;
    protected $validator;
    protected $translator;
    
    public function __construct(UserManager $user_manager, TokenGenerator $tokenGenerator, ValidatorInterface $validator, Translator $translator)
    {
      $this->user_manager = $user_manager;
      $this->tokenGenerator = $tokenGenerator;
      $this->validator = $validator;
      $this->translator = $translator;
    }
  
    public function checkTokenAction()
    {
      return JsonResponse::create(array("statusCode" => 200, "answer" => $this->trans("success.token"), "preHeaderMessages" => "  \n"));
    }
    
    public function loginOrRegisterAction(Request $request)
    {
      $retArray = array();
      $userManager = $this->user_manager;
      $username = $request->request->get('registrationUsername');

      $user = $userManager->findUserByUsername($username);
      
      if ($user == null) 
      {
        $create_request = new CreateUserRequest($request);
        $violations = $this->validator->validate($create_request);
        foreach ($violations as $violation)
        {
          $retArray['statusCode'] = 602;
          $retArray['answer'] = $this->trans($violation->getMessageTemplate(),$violation->getParameters());
          break;
        }
        
        if (count($violations) == 0) 
        {
          $user = $userManager->createUser();
          $user->setUsername($create_request->username);
          $user->setEmail($create_request->mail);
          $user->setPlainPassword($create_request->password);
          $user->setEnabled(true);
          $user->setUploadToken($this->tokenGenerator->generateToken());
          
          $userManager->updateUser($user);
          $retArray['statusCode'] = 201;
          $retArray['answer'] = $this->trans("success.registration");
          $retArray['token'] = $user->getToken();
        }
      } 
      else 
      {
        $retArray['statusCode'] = 200;
        $correct_pass = $userManager->isPasswordValid($user, $request->request->get('registrationPassword'));
        
        if ($correct_pass) 
        {
          $retArray['statusCode'] = 200;
          $retArray['token'] = $user->getUploadToken();
        } 
        else 
        {
          $retArray['statusCode'] = 601;
          $retArray['answer'] = $this->trans("error.login");
          
        }
      }
      $retArray['preHeaderMessages'] = "";
      return JsonResponse::create($retArray);
    }
    
    private function trans($message, $parameters = array())
    {
      return $this->translator->trans($message,$parameters,"catroweb_api");
    }
}
