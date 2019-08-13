<?php

namespace App\Catrobat\Services;

use App\Catrobat\Requests\CreateOAuthUserRequest;
use App\Catrobat\StatusCode;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * Class OAuthService
 * @package App\Catrobat\Services
 */
class OAuthService
{
  /**
   * @var UserManager
   */
  private $user_manager;

  /**
   * @var ParameterBagInterface
   */
  private $parameter_bag;

  /**
   * @var ValidatorInterface
   */
  private $validator;

  /**
   * @var ProgramManager
   */
  private $program_manager;

  /**
   * @var EntityManagerInterface
   */
  private $em;

  /**
   * @var TranslatorInterface
   */
  private $translator;

  /**
   * @var TokenStorageInterface 
   */
  private $token_storage;

  /**
   * @var TokenGenerator
   */
  private $token_generator;

  /**
   * @var EventDispatcherInterface 
   */
  private $dispatcher;

  /**
   * @var RouterInterface 
   */
  private $router;


  /**
   * OAuthService constructor.
   *
   * @param UserManager $user_manager
   * @param ParameterBagInterface $parameter_bag
   * @param ValidatorInterface $validator
   * @param ProgramManager $program_manager
   * @param EntityManagerInterface $em
   * @param TranslatorInterface $translator
   * @param TokenStorageInterface $token_storage
   * @param EventDispatcherInterface $dispatcher
   * @param RouterInterface $router
   * @param TokenGenerator $token_generator
   */
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
   * @param Request $request
   *
   * @return JsonResponse
   * @throws Exception
   */
  public function isOAuthUser(Request $request)
  {
    /**
     * @var $user        User
     */
    $username_email = $request->request->get('username_email');

    $retArray = [];

    $user = $this->user_manager->findOneBy([
      'username' => $username_email,
    ]);
    if (!$user)
    {
      $user = $this->user_manager->findOneBy([
        'email' => $username_email,
      ]);
    }

    if ($user && $user->getGplusUid())
    {
      $retArray['is_oauth_user'] = true;
    }
    else
    {
      $retArray['is_oauth_user'] = false;
    }
    $retArray['statusCode'] = StatusCode::OK;

    return JsonResponse::create($retArray);
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws Exception
   */
  public function checkEMailAvailable(Request $request)
  {
    /**
     * @var $user        User
     */
    $email = $request->request->get('email');

    $retArray = [];

    $user = $this->user_manager->findUserByEmail($email);
    if ($user)
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
   * @param Request $request

   *
   * @return JsonResponse
   * @throws Exception
   */
  public function checkUserNameAvailable(Request $request)
  {
    /**
     * @var $user        User
     */
    $username = $request->request->get('username');

    $retArray = [];

    $user = $this->user_manager->findOneBy([
      'username' => $username,
    ]);

    if ($user)
    {
      $retArray['username_available'] = true;
    }
    else
    {
      $retArray['username_available'] = false;
    }
    $retArray['statusCode'] = StatusCode::OK;

    return JsonResponse::create($retArray);
  }

  /**
   * @param $e \Error
   *
   * @return JsonResponse
   */
  private function returnErrorCode($e)
  {
    $retArray['error_code'] = $e->getCode();
    $retArray['error_description'] = $e->getMessage();

    return JsonResponse::create($retArray);
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws Exception
   */
  public function checkGoogleServerTokenAvailable(Request $request)
  {
    /**
     * @var $google_user User
     */
    $google_id = $request->request->get('id');

    $retArray = [];

    $google_user = $this->user_manager->findOneBy([
      'gplusUid' => $google_id,
    ]);
    if ($google_user && $google_user->getGplusAccessToken())
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
   * @param Request $request
  *
   * @return JsonResponse|Response
   * @throws Exception
   */
  public function exchangeGoogleCodeAction(Request $request)
  {
    /**
     * @var $google_user User
     * @var $user        User
     */

    $retArray = [];

    $client_id = $this->parameter_bag-> get('google_app_id');
    $id_token = $request->request->get('id_token');
    $username = $request->request->get('username');

    $client = new Google_Client(['client_id' => $client_id]);  // Specify the CLIENT_ID of the app that accesses the backend
    $payload = $client->verifyIdToken($id_token);
    if ($payload)
    {
      $gPlusId = $payload['sub'];
      $gEmail = $payload['email'];
      $gName = $payload['name'];
      $gLocale = $payload['locale'];
    }
    else
    {
      return new Response('Token invalid', 777);
    }

    if ($gEmail)
    {
      $user = $this->user_manager->findUserByUsernameOrEmail($gEmail);
    }
    else
    {
      $user = null;
    }
    $google_user = $this->user_manager->findUserBy([
      'gplusUid' => $gPlusId,
    ]);

    if ($google_user)
    {
      $this->setGoogleTokens($google_user, null, null, $id_token);
    }
    else
    {
      if ($user)
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
    }
    return JsonResponse::create($retArray);
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws Exception
   */
  public function loginWithGoogleAction(Request $request)
  {
    /**
     * @var $user        User
     * @var $google_user User
     */
    $retArray = [];

    $google_username = $request->request->get('username');
    $google_id = $request->request->get('id');
    $google_mail = $request->request->get('email');
    $locale = $request->request->get('locale');

    $user = $this->user_manager->findUserByEmail($google_mail);
    $google_user = $this->user_manager->findOneBy([
      'gplusUid' => $google_id,
    ]);
    if ($google_user)
    {
      $google_user->setUploadToken($this->token_generator->generateToken());
      $this->user_manager->updateUser($google_user);
      $retArray['token'] = $google_user->getUploadToken();
      $retArray['username'] = $google_user->getUsername();
      $this->setLoginOAuthUserStatusCode($retArray);
    }
    else
    {
      if ($user)
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
   * @param Request $request
   *
   * @return JsonResponse
   * @throws Exception
   */
  public function getGoogleUserProfileInfo(Request $request)
  {
    /**
     * @var $google_user User
     */

    $retArray = [];

    $google_id = $request->request->get('id');
    $google_user = $this->user_manager->findOneBy([
      'gplusUid' => $google_id,
    ]);

    if ($google_user)
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

  /**
   * @param $user
   * @param $access_token
   * @param $refresh_token
   * @param $id_token
   */
  private function setGoogleTokens($user, $access_token, $refresh_token, $id_token)
  {
    /**
     * @var $user        User
     */
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
   * @param $request
   * @param $retArray
   * @param $user
   * @param $googleId
   * @param $googleUsername
   * @param $locale
   *
   * @throws Exception
   */
  private function connectGoogleUserToExistingUserAccount($request, &$retArray, $user, $googleId, $googleUsername, $locale)
  {
    /**
     * @var $user        User
     */
    $violations = $this->validateOAuthUser($request, $retArray);
    if (count($violations) == 0)
    {
      if ($user->getUsername() == '')
      {
        $user->setUsername($googleUsername);
      }
      if ($user->getCountry() == '')
      {
        $user->setCountry($locale);
      }

      $user->setGplusUid($googleId);

      $user->setEnabled(true);
      $this->user_manager->updateUser($user);
      $retArray['statusCode'] = 201;
      $retArray['answer'] = $this->trans("success.registration");
    }
  }

  /**
   * @param Request     $request
   * @param array       $retArray
   * @param string      $googleId
   * @param string      $googleUsername
   * @param string      $googleEmail
   * @param string      $locale
   * @param string|null $access_token
   * @param string|null $refresh_token
   * @param string|null $id_token
   *
   * @throws Exception
   */
  private function registerGoogleUser($request, &$retArray, $googleId, $googleUsername, $googleEmail,
                                      $locale, $access_token = null, $refresh_token = null, $id_token = null)
  {
    $violations = $this->validateOAuthUser($request, $retArray);
    $retArray['violations'] = count($violations);
    if (count($violations) == 0)
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

//            if ($access_token) {
//                $user->setGplusAccessToken($access_token);
//            }
//            if ($refresh_token) {
//                $user->setGplusRefreshToken($refresh_token);
//            }

      $this->user_manager->updateUser($user);

      $retArray['statusCode'] = 201;
      $retArray['answer'] = $this->trans("success.registration");
    }
  }

  /**
   * @param $user
   */
  private function refreshGoogleAccessToken($user)
  {
    /**
     * @var $user        User
     */
    // Google offline server tokens are valid for ~1h. So, we need to check if the token has to be refreshed
    // before making server-side requests. The refresh token has an unlimited lifetime.
    $server_access_token = $user->getGplusAccessToken();
    $refresh_token = $user->getGplusRefreshToken();

    if ($server_access_token != null && $refresh_token != null)
    {

      $client = $this->getAuthenticatedGoogleClientForGPlusUser($user);

      $reqUrl = 'https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=' . $server_access_token;
      $req = new Google_Http_Request($reqUrl);

      /*
       * result for valid token:
       * {
       * "issued_to": "[app id]",
       * "audience": "[app id]",
       * "user_id": "[user id]",
       * "scope": "https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/plus.moments.write https://www.googleapis.com/auth/plus.me https://www.googleapis.com/auth/plus.profile.agerange.read https://www.googleapis.com/auth/plus.profile.language.read https://www.googleapis.com/auth/plus.circles.members.read https://www.googleapis.com/auth/userinfo.profile",
       * "expires_in": 3181,
       * "email": "[email]",
       * "verified_email": [true/false],
       * "access_type": "offline"
       * }
       * result for invalid token:
       * {
       * "error_description": "Invalid Value"
       * }
       */

      $results = get_object_vars(json_decode($client->getAuth()
        ->authenticatedRequest($req)
        ->getResponseBody()));

      if (isset($results['error_description']) && $results['error_description'] == 'Invalid Value')
      {
        // token is expired --> refresh
        $newtoken_array = json_decode($client->getAccessToken());
        $newtoken = $newtoken_array->access_token;
        $user->setGplusAccessToken($newtoken);
        $this->user_manager->updateUser($user);
      }
    }
  }

  /**
   * @param $user
   *
   * @return Google_Client|Response
   */
  private function getAuthenticatedGoogleClientForGPlusUser($user)
  {
    /**
     * @var $user        User
     */
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
    $client->setAccessToken(json_encode($token_array));
    $client->refreshToken($refresh_token);

    return $client;
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   */
  public function loginWithTokenAndRedirectAction(Request $request)
  {
    /**
     * @var $user        User
     */
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

    if ($user != null)
    {
      $retArray['user'] = true;
      $token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
      $retArray['token'] = $token;
      $this->token_storage->setToken($token);

      // now dispatch the login event
      $event = new InteractiveLoginEvent($request, $token);
      $this->dispatcher->dispatch("security.interactive_login", $event);

      $retArray['url'] = $this->router->generate('index');

      return JsonResponse::create($retArray);
    }

    $retArray['error'] = 'Google User not found!';

    return JsonResponse::create($retArray);
  }

  /**
   * @return string
   */
  function randomPassword()
  {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = []; //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++)
    {
      $n = rand(0, $alphaLength);
      $pass[] = $alphabet[$n];
    }

    return implode($pass); //turn the array into a string
  }

  /**
   * @param $retArray
   */
  private function setLoginOAuthUserStatusCode(&$retArray)
  {
    $retArray['statusCode'] = StatusCode::OK;
  }

  /**
   * @param $request
   * @param $retArray
   *
   * @return mixed
   * @throws Exception
   */
  private function validateOAuthUser($request, &$retArray)
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
   * @return JsonResponse
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function deleteOAuthTestUserAccounts()
  {
    /**
     * @var $user        User
     */
    $retArray = [];

    $deleted = '';

    $google_testuser_mail = $this->parameter_bag->get('google_testuser_mail');
    $google_testuser_username = $this->parameter_bag->get('google_testuser_name');
    $google_testuser_id = $this->parameter_bag->get('google_testuser_id');


    $user = $this->user_manager->findUserByEmail($google_testuser_mail);
    if ($user != null)
    {
      $deleted = $deleted . '_G+-Mail:' . $user->getEmail();
      $this->deleteUser($user);
    }

    $user = $this->user_manager->findUserByUsername($google_testuser_username);
    if ($user != null)
    {
      $deleted = $deleted . '_G+-User' . $user->getUsername();
      $this->deleteUser($user);
    }

    $user = $this->user_manager->findUserBy([
      'gplusUid' => $google_testuser_id,
    ]);
    if ($user != null)
    {
      $deleted = $deleted . '_G+-ID' . $user->getGplusUid();
      $this->deleteUser($user);
    }

    $retArray['deleted'] = $deleted;
    $retArray['statusCode'] = StatusCode::OK;

    return JsonResponse::create($retArray);
  }

  /**
   * @param $user
   *
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws Exception
   */
  private function deleteUser($user)
  {
    /**
     * @var $user            User
     */

    $user_programs = $this->program_manager->getUserPrograms($user->getId(), true);

    foreach ($user_programs as $user_program)
    {
      $this->em->remove($user_program);
      $this->em->flush();
    }

    $this->user_manager->deleteUser($user);
  }

  /**
   * @param       $message
   * @param array $parameters
   *
   * @return string
   * @throws Exception
   */
  private function trans($message, $parameters = [])
  {
    return $this->translator->trans($message, $parameters, 'catroweb');
  }
}
