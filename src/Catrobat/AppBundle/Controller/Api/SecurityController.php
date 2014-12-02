<?php

namespace Catrobat\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\SecurityContext;
use Catrobat\AppBundle\Model\UserManager;
use Catrobat\AppBundle\Services\TokenGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Catrobat\AppBundle\StatusCode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\AppBundle\Requests\CreateUserRequest;

class SecurityController extends Controller
{
  /**
   * @Route("/api/checkToken/check.json", name="catrobat_api_check_token", defaults={"_format": "json"})
   * @Method({"POST"})
   */
  public function checkTokenAction()
  {
    return JsonResponse::create(array("statusCode" => StatusCode::OK, "answer" => $this->trans("success.token"), "preHeaderMessages" => "  \n"));
  }

  /**
   * @Route("/api/loginOrRegister/loginOrRegister.json", name="catrobat_api_login_or_register", defaults={"_format": "json"})
   * @Method({"POST"})
   */
  public function loginOrRegisterAction(Request $request)
  {
    $userManager = $this->get("usermanager");
    $tokenGenerator = $this->get("tokengenerator");
    $validator = $this->get("validator");
    
    $retArray = array();
    $username = $request->request->get('registrationUsername');

    $user = $userManager->findUserByUsername($username);

    if ($user == null)
    {
      $create_request = new CreateUserRequest($request);
      $violations = $validator->validate($create_request);
      foreach ($violations as $violation)
      {
        $retArray['statusCode'] = StatusCode::REGISTRATION_ERROR;
        $retArray['answer'] = $this->trans($violation->getMessageTemplate(),$violation->getParameters());
        break;
      }

      if (count($violations) == 0)
      {
        if ($userManager->findUserByEmail($create_request->mail) != null)
        {
          $retArray['statusCode'] = StatusCode::EMAIL_ALREADY_EXISTS;
          $retArray['answer'] = $this->trans("error.email.exists");
        }
        else 
        {
          $user = $userManager->createUser();
          $user->setUsername($create_request->username);
          $user->setEmail($create_request->mail);
          $user->setPlainPassword($create_request->password);
          $user->setEnabled(true);
          $user->setUploadToken($tokenGenerator->generateToken());
  
          $userManager->updateUser($user);
          $retArray['statusCode'] = 201;
          $retArray['answer'] = $this->trans("success.registration");
          $retArray['token'] = $user->getUploadToken();
        }
      }
    }
    else
    {
      $retArray['statusCode'] = StatusCode::OK;
      $correct_pass = $userManager->isPasswordValid($user, $request->request->get('registrationPassword'));
      if ($correct_pass)
      {
        $retArray['statusCode'] = StatusCode::OK;
        $retArray['token'] = $user->getUploadToken();
      }
      else
      {
        $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
        $retArray['answer'] = $this->trans("error.login");
      }
    }
    $retArray['preHeaderMessages'] = "";
    return JsonResponse::create($retArray);
  }

  private function trans($message, $parameters = array())
  {
    return $this->get("translator")->trans($message,$parameters,"catroweb");
  }
}
