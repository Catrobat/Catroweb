<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\Services\OAuthService;
use App\Catrobat\Services\TestEnv\FakeOAuthService;
use App\Catrobat\Services\TokenGenerator;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\UserManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Catrobat\StatusCode;
use Symfony\Component\Routing\Annotation\Route;
use App\Catrobat\Requests\LoginUserRequest;
use App\Catrobat\Requests\CreateUserRequest;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use App\Catrobat\Security\UserAuthenticator;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * Class SecurityController
 * @package App\Catrobat\Controller\Api
 */
class SecurityController extends AbstractController
{

  /**
   * @var OAuthService|FakeOAuthService
   */
  private $oauth_service;

  /**
   * SecurityController constructor.
   *
   * @param OAuthService $oauth_service
   */
  public function __construct(OAuthService $oauth_service)
  {
    $this->oauth_service = $oauth_service;
  }

  /**
   * @Route("/api/checkToken/check.json", name="catrobat_api_check_token", defaults={"_format": "json"},
   *                                      methods={"POST"})
   * 
   * @param TranslatorInterface $translator
   *
   * @return JsonResponse
   */
  public function checkTokenAction(TranslatorInterface $translator)
  {
    return JsonResponse::create([
      'statusCode'        => StatusCode::OK,
      'answer'            => $translator->trans('success.token', [], 'catroweb'),
      'preHeaderMessages' => "  \n",
    ]);
  }


  /**
   * @deprecated
   *
   * @Route("/api/loginOrRegister/loginOrRegister.json", name="catrobat_api_login_or_register", defaults={"_format":
   *                                                     "json"}, methods={"POST"})
   * @param Request $request
   * @param UserManager $user_manager
   * @param TokenGenerator $token_generator
   * @param TranslatorInterface $translator
   * @param UserAuthenticator $user_authenticator
   * @param ValidatorInterface $validator
   *
   * @return JsonResponse
   */
  public function loginOrRegisterAction(Request $request, UserManager $user_manager, TokenGenerator $token_generator,
                                        TranslatorInterface $translator, UserAuthenticator $user_authenticator,
                                        ValidatorInterface $validator)
  {
    /**
     * @var $user        User
     */
    $retArray = [];

    $this->signInLdapUser($request, $retArray, $user_authenticator, $translator);
    if (array_key_exists('statusCode', $retArray) && ($retArray['statusCode'] === StatusCode::OK || $retArray['statusCode'] === StatusCode::LOGIN_ERROR))
    {
      return JsonResponse::create($retArray);
    }

    $create_request = new CreateUserRequest($request);

    $violations = $validator->validate($create_request);
    foreach ($violations as $violation)
    {
      $retArray['statusCode'] = StatusCode::REGISTRATION_ERROR;
      switch ($violation->getMessageTemplate())
      {
        case 'errors.password.short':
          $retArray['statusCode'] = StatusCode::USER_PASSWORD_TOO_SHORT;
          break;
        case 'errors.email.invalid':
          $retArray['statusCode'] = StatusCode::USER_EMAIL_INVALID;
          break;
      }
      $retArray['answer'] = $translator->trans($violation->getMessageTemplate(), $violation->getParameters(), 'catroweb');

      break;
    }

    if (count($violations) == 0)
    {
      if ($user_manager->findUserByEmail($create_request->mail) != null)
      {
        $retArray['statusCode'] = StatusCode::USER_ADD_EMAIL_EXISTS;
        $retArray['answer'] = $translator->trans('errors.email.exists', [], 'catroweb');
      }
      else
      {
        $user = $user_manager->createUser();
        $user->setUsername($create_request->username);
        $user->setEmail($create_request->mail);
        $user->setPlainPassword($create_request->password);
        $user->setEnabled(true);
        $user->setUploadToken($token_generator->generateToken());
        $user->setCountry($create_request->country);

        $violations = $validator->validate($user);
        if (count($violations) > 0)
        {
          $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
          $retArray['answer'] = $translator->trans('errors.login', [], 'catroweb');
        }
        else
        {
          $user_manager->updateUser($user);
          $retArray['statusCode'] = 201;
          $retArray['answer'] = $translator->trans('success.registration', [], 'catroweb');
          $retArray['token'] = $user->getUploadToken();
        }
      }
    }
    $retArray['preHeaderMessages'] = '';

    return JsonResponse::create($retArray);
  }


  /**
   * @Route("/api/register/Register.json", name="catrobat_api_register", options={"expose"=true}, defaults={"_format":
   *                                       "json"}, methods={"POST"})
   *
   * @param Request $request
   * @param UserManager $user_manager
   * @param TokenGenerator $token_generator
   * @param TranslatorInterface $translator
   * @param ValidatorInterface $validator
   *
   * @return JsonResponse
   */
  public function registerNativeUser(Request $request, UserManager $user_manager, TokenGenerator $token_generator,
                                     TranslatorInterface $translator, ValidatorInterface $validator)
  {
    /**
     * @var $user        User
     */
    $retArray = [];

    $create_request = new CreateUserRequest($request);
    $violations = $validator->validate($create_request);
    foreach ($violations as $violation)
    {
      $retArray['statusCode'] = StatusCode::REGISTRATION_ERROR;
      switch ($violation->getMessageTemplate())
      {
        case 'errors.password.short':
          $retArray['statusCode'] = StatusCode::USER_PASSWORD_TOO_SHORT;
          break;
        case 'errors.email.invalid':
          $retArray['statusCode'] = StatusCode::USER_EMAIL_INVALID;
          break;
      }
      $retArray['answer'] = $translator->trans($violation->getMessageTemplate(), $violation->getParameters(), 'catroweb');
      break;
    }

    if (count($violations) == 0)
    {
      if ($user_manager->findUserByEmail($create_request->mail) != null)
      {
        $retArray['statusCode'] = StatusCode::USER_ADD_EMAIL_EXISTS;
        $retArray['answer'] = $translator->trans('errors.email.exists', [], 'catroweb');
      }
      else
      {
        if ($user_manager->findUserByUsername($create_request->username) != null)
        {
          $retArray['statusCode'] = StatusCode::USER_ADD_USERNAME_EXISTS;
          $retArray['answer'] = $translator->trans('errors.username.exists', [], 'catroweb');
        }
        else
        {
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
    }
    $retArray['preHeaderMessages'] = "";

    return JsonResponse::create($retArray);
  }

  /**
   * @Route("/api/login/Login.json", name="catrobat_api_login", options={"expose"=true}, defaults={"_format": "json"},
   *                                 methods={"POST"})
   * @param Request $request
   * @param UserManager $user_manager
   * @param TokenGenerator $token_generator
   * @param TranslatorInterface $translator
   * @param UserAuthenticator $user_authenticator
   * @param ValidatorInterface $validator
   * @param EncoderFactoryInterface $factory
   *
   * @return JsonResponse
   */
  public function loginNativeUser(Request $request, UserManager $user_manager, TokenGenerator $token_generator,
                                  TranslatorInterface $translator, UserAuthenticator $user_authenticator,
                                  ValidatorInterface $validator, EncoderFactoryInterface $factory)
  {
    /**
     * @var $user        User
     */
    $retArray = [];

    $login_request = new LoginUserRequest($request);
    $violations = $validator->validate($login_request);
    foreach ($violations as $violation)
    {
      $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
      switch ($violation->getMessageTemplate())
      {
        case 'errors.password.short':
          $retArray['statusCode'] = StatusCode::USER_PASSWORD_TOO_SHORT;
          break;
        case 'errors.email.invalid':
          $retArray['statusCode'] = StatusCode::USER_EMAIL_INVALID;
          break;
      }
      $retArray['answer'] = $translator->trans($violation->getMessageTemplate(), $violation->getParameters(), 'catroweb');
      break;
    }

    if (count($violations) > 0)
    {
      $retArray['preHeaderMessages'] = "";

      return JsonResponse::create($retArray);
    }

    if (count($violations) == 0)
    {
      $username = $request->request->get('registrationUsername');
      $password = $request->request->get('registrationPassword');

      $user = $user_manager->findUserByUsername($username);

      if (!$user)
      {
        $this->signInLdapUser($request, $retArray, $user_authenticator, $translator);
        if (array_key_exists('statusCode', $retArray) &&
          ($retArray['statusCode'] === StatusCode::OK
            || $retArray['statusCode'] === StatusCode::LOGIN_ERROR
            || $retArray['statusCode'] === StatusCode::USERNAME_NOT_FOUND
          ))
        {
          return JsonResponse::create($retArray);
        }
        $retArray['statusCode'] = StatusCode::USER_USERNAME_INVALID;
        $retArray['answer'] = $translator->trans('errors.username.exists', [], 'catroweb');
      }
      else
      {
        $encoder = $factory->getEncoder($user);
        $correct_pass = $user_manager->isPasswordValid($user, $password, $encoder);
        $dd = null;
        if ($correct_pass)
        {
          $retArray['statusCode'] = StatusCode::OK;
          $user->setUploadToken($token_generator->generateToken());
          $retArray['token'] = $user->getUploadToken();
          $retArray['email'] = $user->getEmail();
          $user_manager->updateUser($user);
        }
        else
        {
          $this->signInLdapUser($request, $retArray, $user_authenticator, $translator);
          if (array_key_exists('statusCode', $retArray) && ($retArray['statusCode'] === StatusCode::OK || $retArray['statusCode'] === StatusCode::LOGIN_ERROR))
          {
            return JsonResponse::create($retArray);
          }
          $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
          $retArray['answer'] = $translator->trans('errors.login', [], 'catroweb');
        }
      }
    }

    $retArray['preHeaderMessages'] = "";

    return JsonResponse::create($retArray);
  }


  /**
   * @param $request
   * @param $retArray
   * @param UserAuthenticator $authenticator
   * @param TranslatorInterface $translator
   *
   * @return JsonResponse
   */
  private function signInLdapUser($request, &$retArray, UserAuthenticator $authenticator, TranslatorInterface $translator)
  {
    $token = null;
    $username = $request->request->get('registrationUsername');

    try
    {
      $token = $authenticator->authenticate($username, $request->request->get('registrationPassword'));
      $retArray['statusCode'] = StatusCode::OK;
      $retArray['token'] = $token->getUser()->getUploadToken();
      $retArray['preHeaderMessages'] = '';

    } catch (UsernameNotFoundException $exception)
    {
      $retArray['statusCode'] = StatusCode::USERNAME_NOT_FOUND;
      $retArray['answer'] = $translator->trans('errors.username.not_exists', [], 'catroweb');
      $retArray['preHeaderMessages'] = '';
      return JsonResponse::create($retArray);

    } catch (AuthenticationException $exception)
    {
      $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
      $retArray['answer'] = $translator->trans('errors.login', [], 'catroweb');
      $retArray['preHeaderMessages'] = '';
      return JsonResponse::create($retArray);
    }

    return JsonResponse::create($retArray);
  }


  /**
   * @Route("/api/IsOAuthUser/IsOAuthUser.json", name="catrobat_is_oauth_user", options={"expose"=true},
   *                                             defaults={"_format": "json"}, methods={"POST"})
   *
   * @param Request $request
   *
   * @return OAuthService
   * @throws \Exception
   */
  public function isOAuthUser(Request $request)
  {
    return $this->getOAuthService()->isOAuthUser($request);
  }


  /**
   * @Route("/api/EMailAvailable/EMailAvailable.json", name="catrobat_oauth_login_email_available",
   *         options={"expose"=true}, defaults={"_format": "json"}, methods={"POST"})
   *
   * @param Request $request
   *
   * @return OAuthService
   * @throws \Exception
   */
  public function checkEMailAvailable(Request $request)
  {
    return $this->getOAuthService()->checkEMailAvailable($request);
  }


  /**
   * @Route("/api/UsernameAvailable/UsernameAvailable.json", name="catrobat_oauth_login_username_available",
   *         options={"expose"=true}, defaults={"_format": "json"}, methods={"POST"})
   *
   * @param Request $request
   *
   * @return OAuthService
   * @throws \Exception
   */
  public function checkUserNameAvailable(Request $request)
  {
    return $this->getOAuthService()->checkUserNameAvailable($request);
  }


  /**
   * @Route("/api/GoogleServerTokenAvailable/GoogleServerTokenAvailable.json",
   *   name="catrobat_oauth_login_google_servertoken_available", options={"expose"=true},
   *   defaults={"_format": "json"}, methods={"POST"})
   *
   * @param Request $request
   *
   * @return FakeOAuthService|OAuthService
   * @throws \Exception
   */
  public function checkGoogleServerTokenAvailable(Request $request)
  {
    return $this->getOAuthService()->checkGoogleServerTokenAvailable($request);
  }


  /**
   * @Route("/api/exchangeGoogleCode/exchangeGoogleCode.json", name="catrobat_oauth_login_google_code",
   *   options={"expose"=true}, defaults={"_format": "json"}, methods={"POST"})
   *
   * @param Request $request
   *
   * @return FakeOAuthService|OAuthService
   * @throws \Exception
   */
  public function exchangeGoogleCodeAction(Request $request)
  {
    return $this->getOAuthService()->exchangeGoogleCodeAction($request);
  }


  /**
   * @Route("/api/loginWithGoogle/loginWithGoogle.json", name="catrobat_oauth_login_google",
   *   options={"expose"=true}, defaults={"_format": "json"}, methods={"POST"})
   *
   * @param Request $request
   *
   * @return FakeOAuthService|OAuthService
   * @throws \Exception
   */
  public function loginWithGoogleAction(Request $request)
  {
    return $this->getOAuthService()->loginWithGoogleAction($request);
  }


  /**
   * @Route("/api/getGoogleUserInfo/getGoogleUserInfo.json", name="catrobat_google_userinfo",
   *   options={"expose"=true}, defaults={"_format": "json"}, methods={"POST"})
   *
   * @param Request $request
   *
   * @return FakeOAuthService|OAuthService
   * @throws \Exception
   */
  public function getGoogleUserProfileInfo(Request $request)
  {
    return $this->getOAuthService()->getGoogleUserProfileInfo($request);
  }


  /**
   * @Route("/api/loginWithTokenAndRedirect/loginWithTokenAndRedirect", name="catrobat_oauth_login_redirect",
   *   options={"expose"=true}, methods={"POST"})
   *
   * @param Request $request
   *
   * @return FakeOAuthService|OAuthService
   * @throws \Exception
   */
  public function loginWithTokenAndRedirectAction(Request $request)
  {
    return $this->getOAuthService()->loginWithTokenAndRedirectAction($request);
  }


  /**
   * @Route("/api/getGoogleAppId/getGoogleAppId.json", name="catrobat_oauth_login_get_google_appid",
   *   options={"expose"=true}, defaults={"_format": "json"}, methods={"GET"})
   *
   * @return JsonResponse
   */
  public function getGoogleAppId()
  {
    $retArray = [];
    $retArray['gplus_appid'] = $this->getParameter('google_app_id');

    return JsonResponse::create($retArray);
  }


  /**
   * @Route("/api/generateCsrfToken/generateCsrfToken.json", name="catrobat_oauth_register_get_csrftoken",
   *   options={"expose"=true}, defaults={"_format": "json"}, methods={"GET"})
   *
   * @return JsonResponse
   */
  public function generateCsrfToken()
  {
    $retArray = [];
    $retArray['csrf_token'] = $this->container->get('security.csrf.token_manager')
      ->getToken('authenticate')->getValue();

    return JsonResponse::create($retArray);
  }


  /**
   * @Route("/api/deleteOAuthUserAccounts/deleteOAuthUserAccounts.json", name="catrobat_oauth_delete_testusers",
   *   options={"expose"=true}, defaults={"_format":"json"}, methods={"GET"})
   *
   * @return FakeOAuthService|OAuthService
   * @throws \Exception
   */
  public function deleteOAuthTestUserAccounts()
  {
    return $this->getOAuthService()->deleteOAuthTestUserAccounts();
  }


  /**
   * @return FakeOAuthService|OAuthService|object
   */
  private function getOAuthService()
  {
    return $this->oauth_service;
  }
  
}
