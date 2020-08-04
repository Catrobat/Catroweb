<?php

namespace App\Api_deprecated\Controller;

use App\Api_deprecated\Requests\CreateUserRequest;
use App\Api_deprecated\Requests\LoginUserRequest;
use App\Api_deprecated\Security\UserAuthenticator;
use App\Catrobat\Services\OAuthService;
use App\Catrobat\Services\TokenGenerator;
use App\Catrobat\StatusCode;
use App\Entity\User;
use App\Entity\UserManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated
 */
class SecurityController extends AbstractController
{
  private OAuthService $oauth_service;

  public function __construct(OAuthService $oauth_service)
  {
    $this->oauth_service = $oauth_service;
  }

  /**
   * @deprecated
   *
   * @Route("/api/checkToken/check.json", name="catrobat_api_check_token",
   * defaults={"_format": "json"}, methods={"POST"})
   */
  public function checkTokenAction(TranslatorInterface $translator): JsonResponse
  {
    return JsonResponse::create([
      'statusCode' => Response::HTTP_OK,
      'answer' => $translator->trans('success.token', [], 'catroweb'),
      'preHeaderMessages' => "  \n",
    ]);
  }

  /**
   * @deprecated
   *
   * @Route("/api/register/Register.json", name="catrobat_api_register", options={"expose": true},
   * defaults={"_format": "json"}, methods={"POST"})
   */
  public function registerNativeUser(Request $request, UserManager $user_manager, TokenGenerator $token_generator,
                                     TranslatorInterface $translator, ValidatorInterface $validator): JsonResponse
  {
    $retArray = [];

    $create_request = new CreateUserRequest($request);
    $violations = $validator->validate($create_request);
    foreach ($violations as $violation)
    {
      $retArray['statusCode'] = StatusCode::REGISTRATION_ERROR;
      if ('errors.password.short' == $violation->getMessageTemplate())
      {
        $retArray['statusCode'] = StatusCode::USER_PASSWORD_TOO_SHORT;
      }
      elseif ('errors.email.invalid' == $violation->getMessageTemplate())
      {
        $retArray['statusCode'] = StatusCode::USER_EMAIL_INVALID;
      }
      $retArray['answer'] = $translator->trans($violation->getMessageTemplate(), $violation->getParameters(), 'catroweb');
      break;
    }

    if (0 == count($violations))
    {
      if (null != $user_manager->findUserByEmail($create_request->mail))
      {
        $retArray['statusCode'] = StatusCode::USER_ADD_EMAIL_EXISTS;
        $retArray['answer'] = $translator->trans('errors.email.exists', [], 'catroweb');
      }
      elseif (null != $user_manager->findUserByUsername($create_request->username))
      {
        $retArray['statusCode'] = StatusCode::USER_ADD_USERNAME_EXISTS;
        $retArray['answer'] = $translator->trans('errors.username.exists', [], 'catroweb');
      }
      else
      {
        /** @var User $user */
        $user = $user_manager->createUser();
        $user->setUsername($create_request->username);
        $user->setEmail($create_request->mail);
        $user->setPlainPassword($create_request->password);
        $user->setEnabled(true);
        $user->setUploadToken($token_generator->generateToken());
        $user->setCountry($create_request->country);

        $user_manager->updateUser($user);
        $retArray['statusCode'] = 201;
        $retArray['answer'] = $translator->trans('success.registration', [], 'catroweb');
        $retArray['token'] = $user->getUploadToken();
      }
    }
    $retArray['preHeaderMessages'] = '';

    return JsonResponse::create($retArray);
  }

  /**
   * @deprecated
   *
   * @Route("/api/login/Login.json", name="catrobat_api_login", options={"expose": true}, defaults={"_format": "json"},
   * methods={"POST"})
   */
  public function loginNativeUser(Request $request, UserManager $user_manager, TokenGenerator $token_generator,
                                  TranslatorInterface $translator, UserAuthenticator $user_authenticator,
                                  ValidatorInterface $validator, EncoderFactoryInterface $factory): JsonResponse
  {
    $retArray = [];

    $login_request = new LoginUserRequest($request);
    $violations = $validator->validate($login_request);
    foreach ($violations as $violation)
    {
      $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
      if ('errors.password.short' == $violation->getMessageTemplate())
      {
        $retArray['statusCode'] = StatusCode::USER_PASSWORD_TOO_SHORT;
      }
      elseif ('errors.email.invalid' == $violation->getMessageTemplate())
      {
        $retArray['statusCode'] = StatusCode::USER_EMAIL_INVALID;
      }
      $retArray['answer'] = $translator->trans($violation->getMessageTemplate(), $violation->getParameters(), 'catroweb');
      break;
    }

    if (count($violations) > 0)
    {
      $retArray['preHeaderMessages'] = '';

      return JsonResponse::create($retArray);
    }

    if (0 == count($violations))
    {
      $username = $request->request->get('registrationUsername');
      $password = $request->request->get('registrationPassword');

      /** @var User|null $user */
      $user = $user_manager->findUserByUsername($username);

      if (null === $user)
      {
        $retArray['statusCode'] = StatusCode::USERNAME_NOT_FOUND;
        $retArray['answer'] = $translator->trans('errors.username.not_exists', [], 'catroweb');
      }
      else
      {
        $encoder = $factory->getEncoder($user);
        $correct_pass = $user_manager->isPasswordValid($user, $password, $encoder);
        $dd = null;
        if ($correct_pass)
        {
          $retArray['statusCode'] = Response::HTTP_OK;
          $user->setUploadToken($token_generator->generateToken());
          $retArray['token'] = $user->getUploadToken();
          $retArray['email'] = $user->getEmail();
          $user_manager->updateUser($user);
        }
        else
        {
          $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
          $retArray['answer'] = $translator->trans('errors.login', [], 'catroweb');
        }
      }
    }

    $retArray['preHeaderMessages'] = '';

    return JsonResponse::create($retArray);
  }

  /**
   * @deprecated
   *
   * @Route("/api/IsOAuthUser/IsOAuthUser.json", name="catrobat_is_oauth_user", options={"expose": true},
   * defaults={"_format": "json"}, methods={"POST"})
   *
   * @throws Exception
   */
  public function isOAuthUser(Request $request): JsonResponse
  {
    return $this->getOAuthService()->isOAuthUser($request);
  }

  /**
   * @deprecated
   *
   * @Route("/api/EMailAvailable/EMailAvailable.json", name="catrobat_oauth_login_email_available",
   * options={"expose": true}, defaults={"_format": "json"}, methods={"POST"})
   *
   * @throws Exception
   */
  public function checkEMailAvailable(Request $request): JsonResponse
  {
    return $this->getOAuthService()->checkEMailAvailable($request);
  }

  /**
   * @deprecated
   *
   * @Route("/api/UsernameAvailable/UsernameAvailable.json", name="catrobat_oauth_login_username_available",
   * options={"expose": true}, defaults={"_format": "json"}, methods={"POST"})
   *
   * @throws Exception
   */
  public function checkUserNameAvailable(Request $request): JsonResponse
  {
    return $this->getOAuthService()->checkUserNameAvailable($request);
  }

  /**
   * @deprecated
   *
   * @Route("/api/GoogleServerTokenAvailable/GoogleServerTokenAvailable.json",
   *     name="catrobat_oauth_login_google_servertoken_available", options={"expose": true},
   * defaults={"_format": "json"}, methods={"POST"})
   *
   * @throws Exception
   */
  public function checkGoogleServerTokenAvailable(Request $request): JsonResponse
  {
    return $this->getOAuthService()->checkGoogleServerTokenAvailable($request);
  }

  /**
   * @deprecated
   *
   * @Route("/api/exchangeGoogleCode/exchangeGoogleCode.json", name="catrobat_oauth_login_google_code",
   * options={"expose": true}, defaults={"_format": "json"}, methods={"POST"})
   *
   * @throws Exception
   */
  public function exchangeGoogleCodeAction(Request $request): JsonResponse
  {
    return $this->getOAuthService()->exchangeGoogleCodeAction($request);
  }

  /**
   * @deprecated
   *
   * @Route("/api/loginWithGoogle/loginWithGoogle.json", name="catrobat_oauth_login_google",
   * options={"expose": true}, defaults={"_format": "json"}, methods={"POST"})
   *
   * @throws Exception
   */
  public function loginWithGoogleAction(Request $request): JsonResponse
  {
    return $this->getOAuthService()->loginWithGoogleAction($request);
  }

  /**
   * @deprecated
   *
   * @Route("/api/getGoogleUserInfo/getGoogleUserInfo.json", name="catrobat_google_userinfo",
   * options={"expose": true}, defaults={"_format": "json"}, methods={"POST"})
   *
   * @throws Exception
   */
  public function getGoogleUserProfileInfo(Request $request): JsonResponse
  {
    return $this->getOAuthService()->getGoogleUserProfileInfo($request);
  }

  /**
   * @deprecated
   *
   * @Route("/api/loginWithTokenAndRedirect/loginWithTokenAndRedirect", name="catrobat_oauth_login_redirect",
   * options={"expose": true}, methods={"POST"})
   *
   * @throws Exception
   */
  public function loginWithTokenAndRedirectAction(Request $request): JsonResponse
  {
    return $this->getOAuthService()->loginWithTokenAndRedirectAction($request);
  }

  /**
   * @deprecated
   *
   * @Route("/api/getGoogleAppId/getGoogleAppId.json", name="catrobat_oauth_login_get_google_appid",
   * options={"expose": true}, defaults={"_format": "json"}, methods={"GET"})
   */
  public function getGoogleAppId(): JsonResponse
  {
    $retArray = [];
    $retArray['gplus_appid'] = getenv('GOOGLE_CLIENT_ID');

    return JsonResponse::create($retArray);
  }

  /**
   * @deprecated
   *
   * @Route("/api/generateCsrfToken/generateCsrfToken.json", name="catrobat_oauth_register_get_csrftoken",
   * options={"expose": true}, defaults={"_format": "json"}, methods={"GET"})
   */
  public function generateCsrfToken(): JsonResponse
  {
    $retArray = [];
    $retArray['csrf_token'] = $this->container->get('security.csrf.token_manager')
      ->getToken('authenticate')->getValue();

    return JsonResponse::create($retArray);
  }

  /**
   * @deprecated
   *
   * @Route("/api/deleteOAuthUserAccounts/deleteOAuthUserAccounts.json", name="catrobat_oauth_delete_testusers",
   * options={"expose": true}, defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws Exception
   */
  public function deleteOAuthTestUserAccounts(): JsonResponse
  {
    return $this->getOAuthService()->deleteOAuthTestUserAccounts();
  }

  private function getOAuthService(): OAuthService
  {
    return $this->oauth_service;
  }
}
