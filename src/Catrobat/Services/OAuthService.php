<?php

namespace App\Catrobat\Services;

use App\Catrobat\Requests\CreateOAuthUserRequest;
use App\Catrobat\StatusCode;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Google_Client;
use Google_Service_Plus;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OAuthService
{
  private UserManager $user_manager;

  private ParameterBagInterface $parameter_bag;

  private ValidatorInterface $validator;

  private ProgramManager $program_manager;

  private EntityManagerInterface $em;

  private TranslatorInterface $translator;

  private TokenStorageInterface $token_storage;

  private TokenGenerator $token_generator;

  private EventDispatcherInterface $dispatcher;

  private RouterInterface $router;

  public function __construct(UserManager $user_manager, ParameterBagInterface $parameter_bag,
                              ValidatorInterface $validator, ProgramManager $program_manager,
                              EntityManagerInterface $em, TranslatorInterface $translator,
                              TokenStorageInterface $token_storage, EventDispatcherInterface $dispatcher,
                              RouterInterface $router, TokenGenerator $token_generator)
  {
    $this->user_manager = $user_manager;
    $this->parameter_bag = $parameter_bag;
    $this->validator = $validator;
    $this->program_manager = $program_manager;
    $this->translator = $translator;
    $this->em = $em;
    $this->token_storage = $token_storage;
    $this->router = $router;
    $this->dispatcher = $dispatcher;
    $this->token_generator = $token_generator;
  }

  /**
   * @throws Exception
   */
  public function isOAuthUser(Request $request): JsonResponse
  {
    $username_email = $request->request->get('username_email');

    $retArray = [];

    /** @var User|null $user */
    $user = $this->user_manager->findOneBy([
      'username' => $username_email,
    ]);

    if (null === $user)
    {
      $user = $this->user_manager->findOneBy([
        'email' => $username_email,
      ]);
    }

    $retArray['is_oauth_user'] = $user && $user->getGplusUid();
    $retArray['statusCode'] = StatusCode::OK;

    return JsonResponse::create($retArray);
  }

  /**
   * @throws Exception
   */
  public function checkEMailAvailable(Request $request): JsonResponse
  {
    $email = $request->request->get('email');

    $retArray = [];

    /** @var User|null $user */
    $user = $this->user_manager->findUserByEmail($email);
    if (null !== $user)
    {
      $retArray['email_available'] = true;
      $retArray['username'] = $user->getUsername();
    }
    else
    {
      $retArray['email_available'] = false;
    }
    $retArray['statusCode'] = StatusCode::OK;

    return JsonResponse::create($retArray);
  }

  /**
   * @throws Exception
   */
  public function checkUserNameAvailable(Request $request): JsonResponse
  {
    $username = $request->request->get('username');
    $retArray = [];

    /** @var User|null $user */
    $user = $this->user_manager->findOneBy([
      'username' => $username,
    ]);

    $retArray['username_available'] = (bool) $user;
    $retArray['statusCode'] = StatusCode::OK;

    return JsonResponse::create($retArray);
  }

  /**
   * @throws Exception
   */
  public function checkGoogleServerTokenAvailable(Request $request): JsonResponse
  {
    $google_id = $request->request->get('id');
    $retArray = [];

    /** @var User|null $google_user */
    $google_user = $this->user_manager->findOneBy([
      'gplusUid' => $google_id,
    ]);
    if (null !== $google_user)
    {
      $retArray['token_available'] = true;
      $retArray['username'] = $google_user->getUsername();
      $retArray['email'] = $google_user->getEmail();
    }
    else
    {
      $retArray['token_available'] = false;
    }
    $retArray['statusCode'] = StatusCode::OK;

    return JsonResponse::create($retArray);
  }

  /**
   * @throws Exception
   */
  public function exchangeGoogleCodeAction(Request $request): JsonResponse
  {
    $retArray = [];

    $client_id = $this->parameter_bag->get('google_app_id');
    $id_token = $request->request->get('id_token');
    $username = $request->request->get('username');

    $google_user = null;

    try
    {
      $client = new Google_Client(['client_id' => $client_id]);  // Specify the CLIENT_ID of the app that accesses the backend
      $payload = $client->verifyIdToken($id_token);
      if ($payload)
      {
        $gPlusId = $payload['sub'];
        $gEmail = $payload['email'];
        $gLocale = $payload['locale'];
      }
      else
      {
        return new JsonResponse('Token invalid', 777);
      }

      if ($gEmail)
      {
        /** @var User|null $user */
        $user = $this->user_manager->findUserByUsernameOrEmail($gEmail);
      }
      else
      {
        $user = null;
      }
      /** @var User|null $google_user */
      $google_user = $this->user_manager->findUserBy([
        'gplusUid' => $gPlusId,
      ]);
    }
    catch (Exception $exception)
    {
      return new JsonResponse('Token invalid', 777);
    }

    if (null !== $google_user)
    {
      $this->setGoogleTokens($google_user, null, null, $id_token);
    }
    elseif (null !== $user)
    {
      $this->connectGoogleUserToExistingUserAccount($request, $retArray, $user, $gPlusId, $username, $gLocale);
      $this->setGoogleTokens($user, null, null, $id_token);
    }
    else
    {
      $this->registerGoogleUser($request, $retArray, $gPlusId, $username, $gEmail, $gLocale,
        null, null, $id_token);
      $retArray['statusCode'] = 201;
    }

    return JsonResponse::create($retArray);
  }

  /**
   * @throws Exception
   */
  public function loginWithGoogleAction(Request $request): JsonResponse
  {
    $retArray = [];

    $google_username = $request->request->get('username');
    $google_id = $request->request->get('id');
    $google_mail = $request->request->get('email');
    $locale = $request->request->get('locale');

    /** @var User|null $google_user */
    $google_user = $this->user_manager->findOneBy([
      'gplusUid' => $google_id,
    ]);

    if (null !== $google_user)
    {
      $google_user->setUploadToken($this->token_generator->generateToken());
      $this->user_manager->updateUser($google_user);
      $retArray['token'] = $google_user->getUploadToken();
      $retArray['username'] = $google_user->getUsername();
      $this->setLoginOAuthUserStatusCode($retArray);
    }
    else
    {
      /** @var User|null $user */
      $user = $this->user_manager->findUserByEmail($google_mail);

      if (null !== $user)
      {
        $this->connectGoogleUserToExistingUserAccount($request, $retArray, $user, $google_id, $google_username, $locale);
        $user->setUploadToken($this->token_generator->generateToken());
        $this->user_manager->updateUser($user);
        $retArray['token'] = $user->getUploadToken();
        $retArray['username'] = $user->getUsername();
      }
    }

    return JsonResponse::create($retArray);
  }

  /**
   * @throws Exception
   */
  public function getGoogleUserProfileInfo(Request $request): JsonResponse
  {
    $retArray = [];

    $google_id = $request->request->get('id');

    /** @var User|null $google_user */
    $google_user = $this->user_manager->findOneBy([
      'gplusUid' => $google_id,
    ]);

    if (null !== $google_user)
    {
      $this->refreshGoogleAccessToken($google_user);

      $client = $this->getAuthenticatedGoogleClientForGPlusUser($google_user);
      $plus = new Google_Service_Plus($client);
      $person = $plus->people->get($google_id);

      $retArray['ID'] = $person->getId();
      $retArray['displayName'] = $person->getDisplayName();
      $retArray['imageUrl'] = $person->getImage()->getUrl();
      $retArray['profileUrl'] = $person->getUrl();
    }
    else
    {
      $retArray['error'] = 'invalid id';
    }

    return JsonResponse::create($retArray);
  }

  public function loginWithTokenAndRedirectAction(Request $request): JsonResponse
  {
    $retArray = [];

    $user = null;

    if ($request->request->has('gplus_id'))
    {
      $id = $request->request->get('gplus_id');
      $retArray['g_id'] = $id;
      $user = $this->user_manager->findUserBy([
        'gplusUid' => $id,
      ]);
    }

    if (null != $user)
    {
      $retArray['user'] = true;
      $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
      $retArray['token'] = $token;
      $this->token_storage->setToken($token);

      // now dispatch the login event
      $event = new InteractiveLoginEvent($request, $token);
      $this->dispatcher->dispatch($event);

      $retArray['url'] = $this->router->generate('index');

      return JsonResponse::create($retArray);
    }

    $retArray['error'] = 'Google User not found!';

    return JsonResponse::create($retArray);
  }

  public function randomPassword(): string
  {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = []; //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; ++$i)
    {
      $n = random_int(0, $alphaLength);
      $pass[] = $alphabet[$n];
    }

    return implode('', $pass); //turn the array into a string
  }

  /**
   * @throws Exception
   */
  public function deleteOAuthTestUserAccounts(): JsonResponse
  {
    $retArray = [];

    $deleted = '';

    $google_testuser_mail = $this->parameter_bag->get('google_testuser_mail');
    $google_testuser_username = $this->parameter_bag->get('google_testuser_name');
    $google_testuser_id = $this->parameter_bag->get('google_testuser_id');

    /** @var User|null $user */
    $user = $this->user_manager->findUserByEmail($google_testuser_mail);
    if (null !== $user)
    {
      $deleted = $deleted.'_G+-Mail:'.$user->getEmail();
      $this->deleteUser($user);
    }

    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($google_testuser_username);
    if (null !== $user)
    {
      $deleted = $deleted.'_G+-User'.$user->getUsername();
      $this->deleteUser($user);
    }

    /** @var User|null $user */
    $user = $this->user_manager->findUserBy([
      'gplusUid' => $google_testuser_id,
    ]);
    if (null !== $user)
    {
      $deleted = $deleted.'_G+-ID'.$user->getGplusUid();
      $this->deleteUser($user);
    }

    $retArray['deleted'] = $deleted;
    $retArray['statusCode'] = StatusCode::OK;

    return JsonResponse::create($retArray);
  }

  private function setGoogleTokens(User $user, ?string $access_token, ?string $refresh_token, ?string $id_token): void
  {
    if ($access_token)
    {
      $user->setGplusAccessToken($access_token);
    }
    if ($refresh_token)
    {
      $user->setGplusRefreshToken($refresh_token);
    }
    if ($id_token)
    {
      $user->setGplusIdToken($id_token);
    }
    $this->user_manager->updateUser($user);
  }

  /**
   * @param mixed $googleId
   *
   * @throws Exception
   */
  private function connectGoogleUserToExistingUserAccount(Request $request, array &$retArray, User $user, $googleId, string $googleUsername, string $locale): void
  {
    $violations = $this->validateOAuthUser($request, $retArray);
    if (0 === count($violations))
    {
      if ('' === $user->getUsername())
      {
        $locale = substr($locale, 0, 180);
        $user->setUsername($googleUsername);
      }
      if ('' === $user->getCountry() && 'NO_GOOGLE_LOCALE' !== $locale)
      {
        $locale = substr($locale, 0, 5);
        $user->setCountry($locale);
      }

      $user->setGplusUid($googleId);

      $user->setEnabled(true);
      $this->user_manager->updateUser($user);
      $retArray['statusCode'] = 201;
      $retArray['answer'] = $this->trans('success.registration');
    }
  }

  /**
   * @throws Exception
   */
  private function registerGoogleUser(Request $request, array &$retArray, string $googleId, string $googleUsername, string $googleEmail,
                                      string $locale, ?string $access_token = null, ?string $refresh_token = null, ?string $id_token = null): void
  {
    $violations = $this->validateOAuthUser($request, $retArray);
    $retArray['violations'] = count($violations);
    if (0 == count($violations))
    {
      /** @var User $user */
      $user = $this->user_manager->createUser();
      $user->setGplusUid($googleId);
      $user->setUsername($googleUsername);
      $user->setEmail($googleEmail);
      $user->setPlainPassword($this->randomPassword());
      $user->setEnabled(true);
      $user->setCountry($locale);
      if ($id_token)
      {
        $user->setGplusIdToken($id_token);
      }

      $this->user_manager->updateUser($user);

      $retArray['statusCode'] = 201;
      $retArray['answer'] = $this->trans('success.registration');
    }
  }

  /**
   * @throws Exception
   */
  private function refreshGoogleAccessToken(?User $user): void
  {
    throw new Exception('not implemented');
  }

  /**
   * @return Google_Client|Response
   */
  private function getAuthenticatedGoogleClientForGPlusUser(?User $user)
  {
    $application_name = $this->parameter_bag->get('application_name');
    $client_id = $this->parameter_bag->get('google_app_id');
    $client_secret = $this->parameter_bag->get('google_secret');
    // $redirect_uri = 'postmessage';

    if (!$client_secret || !$client_id || !$application_name)
    {
      return new Response('Google app authentication data not found!', 401);
    }

    $server_access_token = $user->getGplusAccessToken();
    $refresh_token = $user->getGplusRefreshToken();

    $client = new Google_Client();
    $client->setApplicationName($application_name);
    $client->setClientId($client_id);
    $client->setClientSecret($client_secret);
    // $client->setRedirectUri($redirect_uri);
    $client->setScopes('https://www.googleapis.com/auth/userinfo.email');
    $client->setState('offline');

    $token_array = [];
    $token_array['access_token'] = $server_access_token;
    $client->setAccessToken(json_encode($token_array, JSON_THROW_ON_ERROR));
    $client->refreshToken($refresh_token);

    return $client;
  }

  private function setLoginOAuthUserStatusCode(array $retArray): void
  {
    $retArray['statusCode'] = StatusCode::OK;
  }

  /**
   * @throws Exception
   */
  private function validateOAuthUser(Request $request, array &$retArray): ConstraintViolationListInterface
  {
    $create_request = new CreateOAuthUserRequest($request);
    $violations = $this->validator->validate($create_request);
    foreach ($violations as $violation)
    {
      $retArray['statusCode'] = StatusCode::REGISTRATION_ERROR;
      $retArray['answer'] = $this->trans($violation->getMessageTemplate(), $violation->getParameters());
      break;
    }

    return $violations;
  }

  /**
   * @throws Exception
   */
  private function deleteUser(?User $user): void
  {
    $user_programs = $this->program_manager->getUserPrograms($user->getId(), true);

    foreach ($user_programs as $user_program)
    {
      $this->em->remove($user_program);
      $this->em->flush();
    }

    $this->user_manager->deleteUser($user);
  }

  /**
   * @throws Exception
   */
  private function trans(string $message, array $parameters = []): string
  {
    return $this->translator->trans($message, $parameters, 'catroweb');
  }
}
