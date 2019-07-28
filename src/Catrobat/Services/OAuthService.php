<?php

namespace App\Catrobat\Services;

use App\Catrobat\Requests\CreateOAuthUserRequest;
use App\Catrobat\StatusCode;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Google_Client;
use Google_Service_Plus;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * Class OAuthService
 * @package App\Catrobat\Services
 */
class OAuthService
{
  /**
   * @var Container
   */
  private $container;

  /**
   * OAuthService constructor.
   *
   * @param Container $container
   */
  public function __construct(Container $container)
  {
    $this->container = $container;
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
     * @var $userManager UserManager
     * @var $user        User
     */
    $username_email = $request->request->get('username_email');

    $userManager = $this->container->get("usermanager");
    $retArray = [];

    $user = $userManager->findOneBy([
      'username' => $username_email,
    ]);
    if (!$user)
    {
      $user = $userManager->findOneBy([
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
     * @var $userManager UserManager
     * @var $user        User
     */
    $email = $request->request->get('email');

    $userManager = $this->container->get("usermanager");
    $retArray = [];

    $user = $userManager->findUserByEmail($email);
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
     * @var $userManager UserManager
     * @var $user        User
     */
    $username = $request->request->get('username');

    $userManager = $this->container->get("usermanager");
    $retArray = [];

    $user = $userManager->findOneBy([
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
   * @param $e
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
     * @var $userManager UserManager
     * @var $google_user User
     */
    $google_id = $request->request->get('id');

    $userManager = $this->container->get("usermanager");
    $retArray = [];

    $google_user = $userManager->findOneBy([
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
     * @var $userManager UserManager
     * @var $google_user User
     * @var $user        User
     */

    $retArray = [];

    $client_id = $this->container->getParameter('google_app_id');
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

    $userManager = $this->container->get("usermanager");
    if ($gEmail)
    {
      $user = $userManager->findUserByUsernameOrEmail($gEmail);
    }
    else
    {
      $user = null;
    }
    $google_user = $userManager->findUserBy([
      'gplusUid' => $gPlusId,
    ]);

    if ($google_user)
    {
      $this->setGoogleTokens($userManager, $google_user, null, null, $id_token);
    }
    else
    {
      if ($user)
      {
        $this->connectGoogleUserToExistingUserAccount($userManager, $request, $retArray, $user, $gPlusId, $username, $gLocale);
        $this->setGoogleTokens($userManager, $user, null, null, $id_token);
      }
      else
      {
        $this->registerGoogleUser($request, $userManager, $retArray, $gPlusId, $username, $gEmail, $gLocale,
          null, null, $id_token);
        $retArray['statusCode'] = 201;
      }
    }

//
//        if (!array_key_exists('statusCode', $retArray) || !$retArray['statusCode'] == StatusCode::LOGIN_ERROR) {
//            $retArray['statusCode'] = 201;
//            $retArray['answer'] = $this->trans("success.registration");
//        }


//        $session = $request->getSession();
//        $code = $request->request->get('code');
//
//        $gPlusId = $request->request->get('id');
//        $google_username = $request->request->get('username');
//        $google_mail = $request->request->get('email');
//        $locale = $request->request->get('locale');
//
//        if (!$request->request->has('mobile')) {
//            $sessionState = $session->get('_csrf/authenticate');
//            $requestState = $request->request->get('state');
//            // Ensure that this is no request forgery going on, and that the user
//            // sending us this request is the user that was supposed to.
//            if (!$sessionState || !$requestState || $sessionState != $requestState) {
//                //return new Response('Invalid state parameter - Session Hijacking attempt?', 401);
//                $retArray['sessionWarning'] = 'Warning: Invalid state parameter - This might be a Session Hijacking attempt!';
//            }
//        }
//
//        $application_name = $this->container->getParameter('application_name');
//        $client_id = $this->container->getParameter('google_app_id');
//        $client_secret = $this->container->getParameter('google_secret');
//        $redirect_uri = 'postmessage';
//
//        if (!$client_secret || !$client_id || !$application_name) {
//            return new Response('Google app authentication data not found!', 401);
//        }

//        $client = new Google_Client();
//        $client->setApplicationName($application_name);
//        $client->setClientId($client_id);
//        $client->setClientSecret($client_secret);
//        if (!$request->request->has('mobile')) {
//            $client->setRedirectUri($redirect_uri);
//        }
//        $client->setScopes('https://www.googleapis.com/auth/userinfo.email');
//
//        $token = '';
//        if ($code) {
//            $client->authenticate($code);
//            $token = json_decode($client->getAccessToken());
//        }
//
//        if (!$token) {
//            return new Response("Google Authentication failed.", 401);
//        }
//
//        $reqUrl = 'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=' . $token->access_token;
//        $req = new Google_Http_Request($reqUrl);
//
//        $results = $client->execute($req);

//        /*
//         * TODO: update to newest G+ PHP API and enabled id token verification
//         *
//         */
//        $id_token = $request->request->get('id_token');
//        try {
//            $ticket = $client->verifyIdToken($id_token);
//            $retArray['id_token_attributes:'] = print_r($ticket->getAttributes(), true);
//            $retArray['id_token_user_id'] = print_r($ticket->getUserId(), true);
//        } catch (\Google_Auth_Exception $e) {
//            return new Response("Invalid id token: " . $e->getMessage(), 401);
//        }


    // Make sure the token we got is for the intended user.

//        if ($results['user_id'] != $gPlusId) {
//            return new Response("Token's user ID doesn't match given user ID", 401);
//        }
//
//        // Make sure the token we got is for our app.
//        if ($results['audience'] != $client_id) {
//            return new Response("Token's client ID does not match app's.", 401);
//        }
//
//        $access_token = $token->access_token;
//        $id_token = $token->id_token;
//        $refresh_token = '';
//        if (property_exists($token, 'refresh_token')) {
//            $refresh_token = $token->refresh_token;
//        }
//
//        // Store the token in the session for later use.
//        // 'Succesfully connected with token: ' . print_r($token, true);
//
//        $userManager = $this->container->get("usermanager");
//        $user = $userManager->findUserByEmail($google_mail);
//        $google_user = $userManager->findUserBy(array(
//            'gplusUid' => $gPlusId
//        ));
//        if ($google_user) {
//            $this->setGoogleTokens($userManager, $google_user, $access_token, $refresh_token, $id_token);
//        } else
//            if ($user) {
//                $this->connectGoogleUserToExistingUserAccount($userManager, $request, $retArray, $user, $gPlusId, $google_username, $locale);
//                $this->setGoogleTokens($userManager, $user, $access_token, $refresh_token, $id_token);
//            } else {
//                $this->registerGoogleUser($request, $userManager, $retArray, $gPlusId, $google_username, $google_mail, $locale, $access_token, $refresh_token, $id_token);
//                $retArray['statusCode'] = 201;
//            }
//
//        if (!array_key_exists('statusCode', $retArray) || !$retArray['statusCode'] == StatusCode::LOGIN_ERROR) {
//            $retArray['statusCode'] = 201;
//            $retArray['answer'] = $this->trans("success.registration");
//        }

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
     * @var $userManager UserManager
     * @var $user        User
     * @var $google_user User
     */
    $userManager = $this->container->get("usermanager");
    $tokenGenerator = $this->container->get('tokengenerator');
    $retArray = [];

    $google_username = $request->request->get('username');
    $google_id = $request->request->get('id');
    $google_mail = $request->request->get('email');
    $locale = $request->request->get('locale');

    $user = $userManager->findUserByEmail($google_mail);
    $google_user = $userManager->findOneBy([
      'gplusUid' => $google_id,
    ]);
    if ($google_user)
    {
      $google_user->setUploadToken($tokenGenerator->generateToken());
      $userManager->updateUser($google_user);
      $retArray['token'] = $google_user->getUploadToken();
      $retArray['username'] = $google_user->getUsername();
      $this->setLoginOAuthUserStatusCode($retArray);
    }
    else
    {
      if ($user)
      {
        $this->connectGoogleUserToExistingUserAccount($userManager, $request, $retArray, $user, $google_id, $google_username, $locale);
        $user->setUploadToken($tokenGenerator->generateToken());
        $userManager->updateUser($user);
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
     * @var $userManager UserManager
     * @var $google_user User
     */
    $userManager = $this->container->get("usermanager");
    $retArray = [];

    $google_id = $request->request->get('id');
    $google_user = $userManager->findOneBy([
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
   * @param $userManager
   * @param $user
   * @param $access_token
   * @param $refresh_token
   * @param $id_token
   */
  private function setGoogleTokens($userManager, $user, $access_token, $refresh_token, $id_token)
  {
    /**
     * @var $userManager UserManager
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
    $userManager->updateUser($user);
  }

  /**
   * @param $userManager
   * @param $request
   * @param $retArray
   * @param $user
   * @param $googleId
   * @param $googleUsername
   * @param $locale
   *
   * @throws Exception
   */
  private function connectGoogleUserToExistingUserAccount($userManager, $request, &$retArray, $user, $googleId, $googleUsername, $locale)
  {
    /**
     * @var $userManager UserManager
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
      $userManager->updateUser($user);
      $retArray['statusCode'] = 201;
      $retArray['answer'] = $this->trans("success.registration");
    }
  }

  /**
   * @param Request     $request
   * @param UserManager $userManager
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
  private function registerGoogleUser($request, $userManager, &$retArray, $googleId, $googleUsername, $googleEmail,
                                      $locale, $access_token = null, $refresh_token = null, $id_token = null)
  {
    $violations = $this->validateOAuthUser($request, $retArray);
    $retArray['violations'] = count($violations);
    if (count($violations) == 0)
    {
      /** @var User $user */
      $user = $userManager->createUser();
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

      $userManager->updateUser($user);

      $retArray['statusCode'] = 201;
      $retArray['answer'] = $this->trans("success.registration");
    }
  }

  /**
   * @param $user
   *
   * @throws Exception
   */
  private function refreshGoogleAccessToken($user)
  {
    /**
     * @var $userManager UserManager
     * @var $user        User
     */
    // Google offline server tokens are valid for ~1h. So, we need to check if the token has to be refreshed
    // before making server-side requests. The refresh token has an unlimited lifetime.
    $userManager = $this->container->get("usermanager");
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
        $userManager->updateUser($user);
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
     * @var $userManager UserManager
     * @var $user        User
     */
    $application_name = $this->container->getParameter('application_name');
    $client_id = $this->container->getParameter('google_app_id');
    $client_secret = $this->container->getParameter('google_secret');
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
   * @throws Exception
   */
  public function loginWithTokenAndRedirectAction(Request $request)
  {
    /**
     * @var $userManager UserManager
     * @var $user        User
     * @var $request     Request
     */
    $userManager = $this->container->get("usermanager");
    $retArray = [];

    $user = null;

    if ($request->request->has('gplus_id'))
    {
      $id = $request->request->get('gplus_id');
      $retArray['g_id'] = $id;
      $user = $userManager->findUserBy([
        'gplusUid' => $id,
      ]);
    }

    if ($user != null)
    {
      $retArray['user'] = true;
      $token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
      $retArray['token'] = $token;
      $this->container->get('security.token_storage')->setToken($token);

      // now dispatch the login event
      $event = new InteractiveLoginEvent($request, $token);
      $this->container->get("event_dispatcher")->dispatch("security.interactive_login", $event);

      $retArray['url'] = $this->container->get('router')->generate('index');

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
    /**
     * @var $validator ValidatorInterface
     */
    $validator = $this->container->get("validator");
    $create_request = new CreateOAuthUserRequest($request);
    $violations = $validator->validate($create_request);
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
   * @throws Exception
   */
  public function deleteOAuthTestUserAccounts()
  {
    /**
     * @var $userManager UserManager
     * @var $user        User
     */
    $userManager = $this->container->get('usermanager');
    $retArray = [];

    $deleted = '';

    $google_testuser_mail = $this->container->getParameter('google_testuser_mail');
    $google_testuser_username = $this->container->getParameter('google_testuser_name');
    $google_testuser_id = $this->container->getParameter('google_testuser_id');


    $user = $userManager->findUserByEmail($google_testuser_mail);
    if ($user != null)
    {
      $deleted = $deleted . '_G+-Mail:' . $user->getEmail();
      $this->deleteUser($user);
    }

    $user = $userManager->findUserByUsername($google_testuser_username);
    if ($user != null)
    {
      $deleted = $deleted . '_G+-User' . $user->getUsername();
      $this->deleteUser($user);
    }

    $user = $userManager->findUserBy([
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
     * @var $user_manager    UserManager
     * @var $user            User
     * @var $program_manager ProgramManager
     * @var $em              EntityManager
     */
    $user_manager = $this->container->get('usermanager');
    $program_manager = $this->container->get('programmanager');
    $em = $this->container->get('doctrine.orm.entity_manager');

    $user_programms = $program_manager->getUserPrograms($user->getId(), true);

    foreach ($user_programms as $user_program)
    {
      $em->remove($user_program);
      $em->flush();
    }

    $user_manager->deleteUser($user);
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
    return $this->container->get('translator')->trans($message, $parameters, 'catroweb');
  }
}
