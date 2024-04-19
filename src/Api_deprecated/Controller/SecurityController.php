<?php

declare(strict_types=1);

namespace App\Api_deprecated\Controller;

use App\Api_deprecated\OAuth\OAuthService;
use App\Api_deprecated\Requests\CreateUserRequest;
use App\Api_deprecated\Requests\LoginUserRequest;
use App\DB\Entity\User\User;
use App\Security\TokenGenerator;
use App\User\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated
 */
class SecurityController extends AbstractController
{
  public function __construct(private readonly OAuthService $oauth_service)
  {
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/checkToken/check.json', name: 'catrobat_api_check_token', defaults: ['_format' => 'json'], methods: ['POST'])]
  public function checkToken(TranslatorInterface $translator): JsonResponse
  {
    return new JsonResponse([
      'statusCode' => Response::HTTP_OK,
      'answer' => $translator->trans('success.token', [], 'catroweb'),
      'preHeaderMessages' => "  \n",
    ]);
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/register/Register.json', name: 'catrobat_api_register', options: ['expose' => true], defaults: ['_format' => 'json'], methods: ['POST'])]
  public function registerNativeUser(Request $request, UserManager $user_manager, TokenGenerator $token_generator, TranslatorInterface $translator, ValidatorInterface $validator): JsonResponse
  {
    $retArray = [];
    $create_request = new CreateUserRequest($request);
    $violations = $validator->validate($create_request);
    foreach ($violations as $violation) {
      $retArray['statusCode'] = Response::HTTP_UNAUTHORIZED;
      if ('errors.password.short' == $violation->getMessageTemplate()) {
        $retArray['statusCode'] = 753;
      } elseif ('errors.email.invalid' == $violation->getMessageTemplate()) {
        $retArray['statusCode'] = 765;
      }
      $retArray['answer'] = $translator->trans($violation->getMessageTemplate(), $violation->getParameters(), 'catroweb');
      break;
    }
    if (0 == count($violations)) {
      if (null != $user_manager->findUserByEmail($create_request->mail)) {
        $retArray['statusCode'] = 757;
        $retArray['answer'] = $translator->trans('errors.email.exists', [], 'catroweb');
      } elseif (null != $user_manager->findUserByUsername($create_request->username)) {
        $retArray['statusCode'] = 777;
        $retArray['answer'] = $translator->trans('errors.username.exists', [], 'catroweb');
      } else {
        /** @var User $user */
        $user = $user_manager->create();
        $user->setUsername($create_request->username);
        $user->setEmail($create_request->mail);
        $user->setPlainPassword($create_request->password);
        $user->setEnabled(true);
        $user->setVerified(false);
        $user->setUploadToken($token_generator->generateToken());

        $user_manager->updateUser($user);
        $retArray['statusCode'] = 201;
        $retArray['answer'] = $translator->trans('success.registration', [], 'catroweb');
        $retArray['token'] = $user->getUploadToken();
      }
    }
    $retArray['preHeaderMessages'] = '';

    return new JsonResponse($retArray);
  }

  /**
   * @deprecated
   */
  #[Route(path: '/api/login/Login.json', name: 'catrobat_api_login', options: ['expose' => true], defaults: ['_format' => 'json'], methods: ['POST'])]
  public function loginNativeUser(Request $request, UserManager $user_manager, TokenGenerator $token_generator, TranslatorInterface $translator, ValidatorInterface $validator): JsonResponse
  {
    $retArray = [];
    $login_request = new LoginUserRequest($request);
    $violations = $validator->validate($login_request);
    foreach ($violations as $violation) {
      $retArray['statusCode'] = 601;
      if ('errors.password.short' == $violation->getMessageTemplate()) {
        $retArray['statusCode'] = 753;
      } elseif ('errors.email.invalid' == $violation->getMessageTemplate()) {
        $retArray['statusCode'] = 765;
      }
      $retArray['answer'] = $translator->trans($violation->getMessageTemplate(), $violation->getParameters(), 'catroweb');
      break;
    }
    if (count($violations) > 0) {
      $retArray['preHeaderMessages'] = '';

      return new JsonResponse($retArray);
    }
    $username = (string) $request->request->get('registrationUsername');
    $password = (string) $request->request->get('registrationPassword');
    /** @var User|null $user */
    $user = $user_manager->findUserByUsername($username);
    if (null === $user) {
      $retArray['statusCode'] = 803;
      $retArray['answer'] = $translator->trans('errors.username.not_exists', [], 'catroweb');
    } else {
      $correct_pass = $user_manager->isPasswordValid($user, $password);
      if ($correct_pass) {
        $retArray['statusCode'] = Response::HTTP_OK;
        $user->setUploadToken($token_generator->generateToken());
        $retArray['token'] = $user->getUploadToken();
        $retArray['email'] = $user->getEmail();
        $user_manager->updateUser($user);
      } else {
        $retArray['statusCode'] = 601;
        $retArray['answer'] = $translator->trans('errors.login', [], 'catroweb');
      }
    }
    $retArray['preHeaderMessages'] = '';

    return new JsonResponse($retArray);
  }

  /**
   * @deprecated
   *
   * @throws \Exception
   */
  #[Route(path: '/api/EMailAvailable/EMailAvailable.json', name: 'catrobat_oauth_login_email_available', options: ['expose' => true], defaults: ['_format' => 'json'], methods: ['POST'])]
  public function checkEMailAvailable(Request $request): JsonResponse
  {
    return $this->getOAuthService()->checkEMailAvailable($request);
  }

  /**
   * @deprecated
   *
   * @throws \Exception
   */
  #[Route(path: '/api/UsernameAvailable/UsernameAvailable.json', name: 'catrobat_oauth_login_username_available', options: ['expose' => true], defaults: ['_format' => 'json'], methods: ['POST'])]
  public function checkUserNameAvailable(Request $request): JsonResponse
  {
    return $this->getOAuthService()->checkUserNameAvailable($request);
  }

  /**
   * @deprecated
   *
   * @throws \Exception
   */
  #[Route(path: '/api/GoogleServerTokenAvailable/GoogleServerTokenAvailable.json', name: 'catrobat_oauth_login_google_servertoken_available', options: ['expose' => true], defaults: ['_format' => 'json'], methods: ['POST'])]
  public function checkGoogleServerTokenAvailable(Request $request): JsonResponse
  {
    return $this->getOAuthService()->checkGoogleServerTokenAvailable($request);
  }

  /**
   * @deprecated
   *
   * @throws \Exception
   */
  #[Route(path: '/api/exchangeGoogleCode/exchangeGoogleCode.json', name: 'catrobat_oauth_login_google_code', options: ['expose' => true], defaults: ['_format' => 'json'], methods: ['POST'])]
  public function exchangeGoogleCode(Request $request): JsonResponse
  {
    return $this->getOAuthService()->exchangeGoogleCodeAction($request);
  }

  /**
   * @deprecated
   *
   * @throws \Exception
   */
  #[Route(path: '/api/loginWithGoogle/loginWithGoogle.json', name: 'catrobat_oauth_login_google', options: ['expose' => true], defaults: ['_format' => 'json'], methods: ['POST'])]
  public function loginWithGoogle(Request $request): JsonResponse
  {
    return $this->getOAuthService()->loginWithGoogleAction($request);
  }

  private function getOAuthService(): OAuthService
  {
    return $this->oauth_service;
  }
}
