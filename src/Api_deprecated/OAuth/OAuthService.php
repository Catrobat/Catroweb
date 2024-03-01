<?php

namespace App\Api_deprecated\OAuth;

use App\DB\Entity\User\User;
use App\Security\PasswordGenerator;
use App\Security\TokenGenerator;
use App\User\UserManager;
use Google\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OAuthService
{
  public function __construct(private readonly UserManager $user_manager, private readonly ValidatorInterface $validator, private readonly TranslatorInterface $translator, private readonly TokenGenerator $token_generator)
  {
  }

  /**
   * @throws \Exception
   */
  public function checkEMailAvailable(Request $request): JsonResponse
  {
    $email = (string) $request->request->get('email');

    $retArray = [];

    /** @var User|null $user */
    $user = $this->user_manager->findUserByEmail($email);
    if (null !== $user) {
      $retArray['email_available'] = true;
      $retArray['username'] = $user->getUsername();
    } else {
      $retArray['email_available'] = false;
    }
    $retArray['statusCode'] = Response::HTTP_OK;

    return new JsonResponse($retArray);
  }

  /**
   * @throws \Exception
   */
  public function checkUserNameAvailable(Request $request): JsonResponse
  {
    $username = (string) $request->request->get('username');
    $retArray = [];

    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($username);

    $retArray['username_available'] = (bool) $user;
    $retArray['statusCode'] = Response::HTTP_OK;

    return new JsonResponse($retArray);
  }

  /**
   * @throws \Exception
   */
  public function checkGoogleServerTokenAvailable(Request $request): JsonResponse
  {
    $google_id = $request->request->get('id');
    $retArray = [];

    /** @var User|null $google_user */
    $google_user = $this->user_manager->findOneBy([
      'google_id' => $google_id,
    ]);
    if (null !== $google_user) {
      $retArray['token_available'] = true;
      $retArray['username'] = $google_user->getUsername();
      $retArray['email'] = $google_user->getEmail();
    } else {
      $retArray['token_available'] = false;
    }
    $retArray['statusCode'] = Response::HTTP_OK;

    return new JsonResponse($retArray);
  }

  /**
   * @throws \Exception
   */
  public function exchangeGoogleCodeAction(Request $request): JsonResponse
  {
    $retArray = [];

    $client_id = getenv('GOOGLE_CLIENT_ID');
    $id_token = (string) $request->request->get('id_token');
    $username = (string) $request->request->get('username');

    try {
      $client = new Client(['client_id' => $client_id]);  // Specify the CLIENT_ID of the app that accesses the backend
      $payload = $client->verifyIdToken($id_token);
      if ($payload) {
        $gPlusId = $payload['sub'];
        $gEmail = $payload['email'];
        $gLocale = $payload['locale'];
      } else {
        return new JsonResponse('Token invalid', 777);
      }

      if ($gEmail) {
        /** @var User|null $user */
        $user = $this->user_manager->findUserByUsernameOrEmail($gEmail);
      } else {
        $user = null;
      }
      /** @var User|null $google_user */
      $google_user = $this->user_manager->findOneBy([
        'google_id' => $gPlusId,
      ]);
    } catch (\Exception) {
      return new JsonResponse('Token invalid', 777);
    }

    if (null !== $google_user) {
      $this->setGoogleTokens($google_user, null, null, $id_token);
    } elseif (null !== $user) {
      $this->connectGoogleUserToExistingUserAccount($request, $retArray, $user, $gPlusId, $username, $gLocale);
      $this->setGoogleTokens($user, null, null, $id_token);
    } else {
      $this->registerGoogleUser($request, $retArray, $gPlusId, $username, $gEmail, $id_token);
      $retArray['statusCode'] = 201;
    }

    return new JsonResponse($retArray);
  }

  /**
   * @throws \Exception
   */
  public function loginWithGoogleAction(Request $request): JsonResponse
  {
    $retArray = [];

    $google_username = (string) $request->request->get('username');
    $google_id = (string) $request->request->get('id');
    $google_mail = (string) $request->request->get('email');
    $locale = (string) $request->request->get('locale');

    /** @var User|null $google_user */
    $google_user = $this->user_manager->findOneBy([
      'google_id' => $google_id,
    ]);

    if (null !== $google_user && '' !== $google_id) {
      $google_user->setUploadToken($this->token_generator->generateToken());
      $this->user_manager->updateUser($google_user);
      $retArray['token'] = $google_user->getUploadToken();
      $retArray['username'] = $google_user->getUsername();
      $retArray['statusCode'] = Response::HTTP_OK;
    } elseif ('' !== $google_mail && '' !== $google_username) {
      /** @var User|null $user */
      $user = $this->user_manager->findUserByEmail($google_mail);

      if (null !== $user) {
        $this->connectGoogleUserToExistingUserAccount($request, $retArray, $user, $google_id, $google_username, $locale);
        $user->setUploadToken($this->token_generator->generateToken());
        $this->user_manager->updateUser($user);
        $retArray['token'] = $user->getUploadToken();
        $retArray['username'] = $user->getUsername();
        $retArray['statusCode'] = Response::HTTP_OK;
      }
    }

    return new JsonResponse($retArray);
  }

  private function setGoogleTokens(User $user, ?string $access_token, ?string $refresh_token, ?string $id_token): void
  {
    if ($access_token) {
      $user->setGplusAccessToken($access_token);
    }
    if ($refresh_token) {
      $user->setGplusRefreshToken($refresh_token);
    }
    if ($id_token) {
      $user->setGplusIdToken($id_token);
    }
    $this->user_manager->updateUser($user);
  }

  /**
   * @throws \Exception
   */
  private function connectGoogleUserToExistingUserAccount(Request $request, array &$retArray, User $user, mixed $googleId, string $googleUsername, string $locale): void
  {
    $violations = $this->validateOAuthUser($request, $retArray);
    if (0 === count($violations)) {
      if ('' === $user->getUsername()) {
        $locale = substr($locale, 0, 180);

        if ($user->getUsername() != $googleUsername) {
          if ($this->user_manager->findUserByUsername($googleUsername)) {
            $username = PasswordGenerator::generateRandomPassword();
          } else {
            $username = $googleUsername;
          }
          $user->setUsername($username);
        }
      }
      if ('NO_GOOGLE_LOCALE' !== $locale) {
        $locale = substr($locale, 0, 5);
      }

      $user->setGoogleId($googleId);

      $user->setEnabled(true);
      $this->user_manager->updateUser($user);
      $retArray['statusCode'] = 201;
      $retArray['answer'] = $this->trans('success.registration');
    }
  }

  /**
   * @throws \Exception
   */
  private function registerGoogleUser(Request $request, array &$retArray, string $googleId,
    string $googleUsername, string $googleEmail, ?string $id_token = null): void
  {
    if ($this->user_manager->findUserByUsername($googleUsername)) {
      $username = PasswordGenerator::generateRandomPassword();
    } else {
      $username = $googleUsername;
    }

    $violations = $this->validateOAuthUser($request, $retArray);
    $retArray['violations'] = count($violations);
    if (0 == count($violations)) {
      /** @var User $user */
      $user = $this->user_manager->create();
      $user->setGoogleId($googleId);
      $user->setUsername($username);
      $user->setEmail($googleEmail);
      $user->setPlainPassword(PasswordGenerator::generateRandomPassword());
      $user->setEnabled(true);
      if ($id_token) {
        $user->setGplusIdToken($id_token);
      }

      $this->user_manager->updateUser($user);

      $retArray['statusCode'] = 201;
      $retArray['answer'] = $this->trans('success.registration');
    }
  }

  /**
   * @throws \Exception
   */
  private function validateOAuthUser(Request $request, array &$retArray): ConstraintViolationListInterface
  {
    $create_request = new CreateOAuthUserRequest($request);
    $violations = $this->validator->validate($create_request);
    foreach ($violations as $violation) {
      $retArray['statusCode'] = Response::HTTP_UNAUTHORIZED;
      $retArray['answer'] = $this->trans($violation->getMessageTemplate(), $violation->getParameters());
      break;
    }

    return $violations;
  }

  /**
   * @throws \Exception
   */
  private function trans(string $message, array $parameters = []): string
  {
    return $this->translator->trans($message, $parameters, 'catroweb');
  }
}
