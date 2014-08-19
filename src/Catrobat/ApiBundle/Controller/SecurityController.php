<?php

namespace Catrobat\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\SecurityContext;
use Catrobat\CoreBundle\Model\UserManager;
use Catrobat\CoreBundle\Services\TokenGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Catrobat\ApiBundle\Requests\CreateUserRequest;
use Symfony\Component\Translation\Translator;
use Catrobat\CoreBundle\StatusCode;

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
    return JsonResponse::create(array("statusCode" => StatusCode::OK, "answer" => $this->trans("success.token"), "preHeaderMessages" => "  \n"));
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
        $retArray['statusCode'] = StatusCode::REGISTRATION_ERROR;
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
        $retArray['token'] = $user->getUploadToken();
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
    return $this->translator->trans($message,$parameters,"catroweb_api");
  }
}
