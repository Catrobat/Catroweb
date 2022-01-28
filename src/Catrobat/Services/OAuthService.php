<?php

namespace App\Catrobat\Services;

use App\Catrobat\Requests\CreateOAuthUserRequest;
use App\Catrobat\StatusCode;
use App\Entity\User;
use App\Manager\ProgramManager;
use App\Manager\UserManager;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Google\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
  public function checkEMailAvailable(Request $request): JsonResponse
  {
    $email = $request->request->get('email');

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
    $retArray['statusCode'] = Response::HTTP_OK;

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
    if (null !== $google_user) {
      $retArray['token_available'] = true;
      $retArray['username'] = $google_user->getUsername();
      $retArray['email'] = $google_user->getEmail();
    } else {
      $retArray['token_available'] = false;
    }
    $retArray['statusCode'] = Response::HTTP_OK;

    return JsonResponse::create($retArray);
  }

  /**
   * @throws Exception
   */
  public function exchangeGoogleCodeAction(Request $request): JsonResponse
  {
    $retArray = [];

    $client_id = getenv('GOOGLE_CLIENT_ID');
    $id_token = $request->request->get('id_token');
    $username = $request->request->get('username');

    $google_user = null;

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
      $google_user = $this->user_manager->findUserBy([
        'gplusUid' => $gPlusId,
      ]);
    } catch (Exception $exception) {
      return new JsonResponse('Token invalid', 777);
    }

    if (null !== $google_user) {
      $this->setGoogleTokens($google_user, null, null, $id_token);
    } elseif (null !== $user) {
      $this->connectGoogleUserToExistingUserAccount($request, $retArray, $user, $gPlusId, $username, $gLocale);
      $this->setGoogleTokens($user, null, null, $id_token);
    } else {
      $this->registerGoogleUser($request, $retArray, $gPlusId, $username, $gEmail, $gLocale, $id_token);
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

    if (null !== $google_user && null !== $google_id && '' !== $google_id) {
      $google_user->setUploadToken($this->token_generator->generateToken());
      $this->user_manager->updateUser($google_user);
      $retArray['token'] = $google_user->getUploadToken();
      $retArray['username'] = $google_user->getUsername();
      $retArray['statusCode'] = Response::HTTP_OK;
    } elseif (null !== $google_mail && '' !== $google_mail && null !== $google_username && '' !== $google_username) {
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

    return JsonResponse::create($retArray);
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
   * @param mixed $googleId
   *
   * @throws Exception
   */
  private function connectGoogleUserToExistingUserAccount(Request $request, array &$retArray, User $user, $googleId, string $googleUsername, string $locale): void
  {
    $violations = $this->validateOAuthUser($request, $retArray);
    if (0 === count($violations)) {
      if ('' === $user->getUsername()) {
        $locale = substr($locale, 0, 180);
        $user->setUsername($googleUsername);
      }
      if ('NO_GOOGLE_LOCALE' !== $locale) {
        $locale = substr($locale, 0, 5);
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
  private function registerGoogleUser(Request $request, array &$retArray, string $googleId,
                                      string $googleUsername, string $googleEmail,
                                      string $locale, ?string $id_token = null): void
  {
    $violations = $this->validateOAuthUser($request, $retArray);
    $retArray['violations'] = count($violations);
    if (0 == count($violations)) {
      /** @var User $user */
      $user = $this->user_manager->createUser();
      $user->setGplusUid($googleId);
      $user->setUsername($googleUsername);
      $user->setEmail($googleEmail);
      $user->setPlainPassword(Utils::randomPassword());
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
   * @throws Exception
   */
  private function validateOAuthUser(Request $request, array &$retArray): ConstraintViolationListInterface
  {
    $create_request = new CreateOAuthUserRequest($request);
    $violations = $this->validator->validate($create_request);
    foreach ($violations as $violation) {
      $retArray['statusCode'] = StatusCode::REGISTRATION_ERROR;
      $retArray['answer'] = $this->trans($violation->getMessageTemplate(), $violation->getParameters());
      break;
    }

    return $violations;
  }

  /**
   * @throws Exception
   */
  private function trans(string $message, array $parameters = []): string
  {
    return $this->translator->trans($message, $parameters, 'catroweb');
  }
}
