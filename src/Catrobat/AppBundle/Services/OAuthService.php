<?php

namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Requests\CreateOAuthUserRequest;
use Catrobat\AppBundle\StatusCode;
use DateTime;
use Doctrine\ORM\EntityManager;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookResponse;
use Google_Client;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;


/**
 * Class OAuthService
 * @package Catrobat\AppBundle\Services
 */
class OAuthService
{
  /**
   * @var Container
   */
  private $container;
  /**
   * @var
   */
  private $facebook;

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
   * @throws \Exception
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

    if ($user && ($user->getFacebookUid() || $user->getGplusUid()))
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
   * @throws \Exception
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
   * @throws \Exception
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
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Exception
   */
  public function checkFacebookServerTokenAvailable(Request $request)
  {
    /**
     * @var $userManager   UserManager
     * @var $facebook_user User
     */
    $facebook_id = $request->request->get('id');

    $userManager = $this->container->get("usermanager");
    $retArray = [];

    $facebook_user = $userManager->findOneBy([
      'facebookUid' => $facebook_id,
    ]);
    if ($facebook_user && $facebook_user->getFacebookAccessToken())
    {
      $retArray['token_available'] = true;
      $retArray['username'] = $facebook_user->getUsername();
      $retArray['email'] = $facebook_user->getEmail();
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
   * @return JsonResponse
   * @throws \Exception
   */
  public function exchangeFacebookTokenAction(Request $request)
  {
    /**
     * @var $userManager   UserManager
     * @var $facebook_user User
     * @var $user          User
     * @var $response      FacebookResponse
     */
    $retArray = [];
    $session = $request->getSession();
    $client_token = $request->request->get('client_token');
    $sessionState = $session->get('_csrf/authenticate');
    $requestState = $request->request->get('state');

    $facebookId = $request->request->get('id');
    $facebook_username = $request->request->get('username');
    $facebook_mail = $request->request->get('email');
    $locale = $request->request->get('locale');

    // Ensure that this is no request forgery going on, and that the user
    // sending us this request is the user that was supposed to.
    if (!$request->request->has('mobile'))
    {
      if (!$sessionState || !$requestState || $sessionState != $requestState)
      {
        //return new Response('Invalid state parameter', 401);
        $retArray['sessionWarning'] = 'Warning: Invalid state parameter - This might be a Session Hijacking attempt!';
      }
    }

    $application_name = $this->container->getParameter('application_name');
    $app_id = $this->container->getParameter('facebook_app_id');
    $client_secret = $this->container->getParameter('facebook_secret');

    if (!$client_secret || !$app_id || !$application_name)
    {
      return new Response('Facebook app authentication data not found!', 401);
    }

    $this->initializeFacebook();

    if ($request->request->has('mobile'))
    {
      $this->setFacebookDefaultAccessToken($client_token);
    }
    else
    {
      $this->setFacebookDefaultAccessToken();
    }

    try
    {
      $response = $this->facebook->post('/oauth/access_token', ['grant_type' => 'fb_exchange_token',
                                                                'client_id'  => $app_id, 'client_secret' => $client_secret, 'fb_exchange_token' => $client_token]);
      $graph_node = $response->getGraphNode();
      $server_token = $graph_node->getField('access_token');
    } catch (FacebookResponseException $exception)
    {
      return new Response(
        "Graph API returned an error during token exchange for 'GET', '/oauth/access_token'", 401);
    } catch (\Exception $exception)
    {
      return new Response(
        "Error during token exchange for 'GET', '/oauth/access_token' with exception" . $exception, 401);
    }

    try
    {
      $token_graph_node = $this->checkFacebookServerAccessTokenValidity($server_token);
      $app_id_debug = $token_graph_node->getField('app_id');
      $application_name_debug = $token_graph_node->getField('application');
      $facebookId_debug = $token_graph_node->getField('user_id');
    } catch (FacebookResponseException $e)
    {
      return new Response(
        "Graph API returned an error during token exchange for 'GET', '/debug_token'", 401);
    } catch (FacebookSDKException $e)
    {
      return new Response(
        "Error during token exchange for 'GET', '/debug_token' with exception" . $e, 401);
    }

    // Make sure the token we got is for the intended user.
    if ($facebookId_debug != $facebookId)
    {
      return new Response("Token's user ID doesn't match given user ID", 401);
    }

    // Make sure the token we got is for our app.
    if ($app_id_debug != $app_id || $application_name_debug != $application_name)
    {
      return new Response("Token's client ID or app name does not match app's.", 401);
    }

    $userManager = $this->container->get("usermanager");
    $user = $userManager->findUserByEmail($facebook_mail);
    $facebook_user = $userManager->findUserBy(['facebookUid' => $facebookId]);

    if ($facebook_user)
    {
      $facebook_user->setFacebookAccessToken($server_token);
      $userManager->updateUser($user);
    }
    else
    {
      if ($user)
      {
        $this->connectFacebookUserToExistingUserAccount($userManager, $request, $retArray, $user, $facebookId, $facebook_username, $locale);
        $user->setFacebookAccessToken($server_token);
        $userManager->updateUser($user);
      }
      else
      {
        $this->registerFacebookUser($request, $userManager, $retArray, $facebookId, $facebook_username, $facebook_mail, $locale, $server_token);
      }
    }

    if (!array_key_exists('statusCode', $retArray) || !$retArray['statusCode'] == StatusCode::LOGIN_ERROR)
    {
      $retArray['statusCode'] = 201;
      $retArray['answer'] = $this->trans("success.registration");
    }

    return JsonResponse::create($retArray);
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Exception
   */
  public function loginWithFacebookAction(Request $request)
  {
    /**
     * @var $userManager UserManager
     * @var $fb_user     User
     * @var $user        User
     */
    $userManager = $this->container->get("usermanager");
    $tokenGenerator = $this->container->get('tokengenerator');
    $retArray = [];

    $fb_username = $request->request->get('username');
    $fb_id = $request->request->get('id');
    $fb_mail = $request->request->get('email');
    $locale = $request->request->get('locale');

    $user = $userManager->findUserByEmail($fb_mail);
    $fb_user = $userManager->findOneBy([
      'facebookUid' => $fb_id,
    ]);
    if ($fb_user)
    {
      $fb_user->setUploadToken($tokenGenerator->generateToken());
      $userManager->updateUser($fb_user);
      $retArray['token'] = $fb_user->getUploadToken();
      $retArray['username'] = $fb_user->getUsername();
      $this->setLoginOAuthUserStatusCode($retArray);
    }
    else
    {
      if ($user)
      {
        $this->connectFacebookUserToExistingUserAccount($userManager, $request, $retArray, $user, $fb_id, $fb_username, $locale);
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
   * @throws \Exception
   */
  public function getFacebookUserProfileInfo(Request $request)
  {
    /**
     * @var $userManager   UserManager
     * @var $facebook      Facebook
     * @var $facebook_user User
     */
    $userManager = $this->container->get("usermanager");
    $retArray = [];

    $facebook_id = $request->request->get('id');
    $facebook_user = $userManager->findOneBy([
      'facebookUid' => $facebook_id,
    ]);

    $client_token = null;
    if ($request->request->has('token'))
    {
      $client_token = $request->request->get('token');
    }

    if ($client_token == null && $facebook_user != null)
    {
      $client_token = $facebook_user->getFacebookAccessToken();
    }

    $this->setFacebookDefaultAccessToken($client_token);

    try
    {
      $facebook = $this->facebook;
      $this->initializeFacebook();
      $user = $facebook->get('/' . $facebook_id . '?fields=id,name,first_name,last_name,link,email,locale')->getGraphUser();
      $retArray['id'] = $user->getId();
      $retArray['first_name'] = $user->getFirstName();
      $retArray['last_name'] = $user->getLastName();
      $retArray['username'] = $user->getName();
      $retArray['link'] = $user->getLink();
      $retArray['locale'] = $user->getField('locale');
      $retArray['email'] = $user->getEmail();
    } catch (FacebookResponseException $e)
    {
      return $this->returnErrorCode($e);
    } catch (FacebookSDKException $e)
    {
      return $this->returnErrorCode($e);
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
   * @return bool|Response
   * @throws FacebookSDKException
   */
  private function initializeFacebook()
  {
    if ($this->facebook != null)
    {
      return true;
    }

    $app_id = $this->container->getParameter('facebook_app_id');
    $client_secret = $this->container->getParameter('facebook_secret');

    if (!$client_secret || !$app_id)
    {
      return new Response('Facebook app authentication data not found!', 401);
    }

    $this->facebook = new Facebook([
      'app_id'                => $app_id,
      'app_secret'            => $client_secret,
      'default_graph_version' => 'v2.5',
    ]);

    return true;
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Exception
   */
  public function isFacebookServerAccessTokenValid(Request $request)
  {
    /**
     * @var $userManager UserManager
     * @var $fb_user     User
     */
    $userManager = $this->container->get("usermanager");
    $retArray = [];

    $facebook_id = $request->request->get('id');

    $fb_user = $userManager->findOneBy([
      'facebookUid' => $facebook_id,
    ]);
    if (!$fb_user)
    {
      // should not happen, but who knows
      $retArray['token_invalid'] = true;
      $retArray['reason'] = 'No Facebook User with given ID in database';
      $retArray['statusCode'] = StatusCode::OK;

      return JsonResponse::create($retArray);
    }

    $app_token = $this->getFacebookAppToken();
    $server_token_to_check = $fb_user->getFacebookAccessToken();

    $this->setFacebookDefaultAccessToken($app_token);
    $result = $this->checkFacebookServerAccessTokenValidity($server_token_to_check);

    /*result:
     * data:
     *  app_id
     *  application
     *  expires at
     *  is_valid
     *  issued_at
     *  scopes
     *      profile, email ...
     *  user_id
     */

    /*
    foreach ($result->getFieldNames() as $field) {
        $retArray['field:' . $field] = $field;
    }

    $retArray['debug_array:'] = print_r($result, true);
    */

    if ($result->getField('error') !== null)
    {
      $error = $result->getField('error');
      $retArray['token_invalid'] = true;
      $retArray['reason'] = 'There was an error during Facebook token check';
      $retArray['details'] = 'Error code: ' . $error->code . ', error subcode: ' . $error->subcode . ', error message: ' . $error->message;
      $retArray['statusCode'] = StatusCode::OK;

      return JsonResponse::create($retArray);
    }

    $application_name = $this->container->getParameter('application_name');
    $app_id = $this->container->getParameter('facebook_app_id');

    $is_valid = $result->getField('is_valid');
    $expires = $result->getField('expires_at');
    $app_id_debug = $result->getField('app_id');
    $application_name_debug = $result->getField('application');
    $facebook_id_debug = $result->getField('user_id');

    if ($app_id_debug != $app_id || $application_name_debug != $application_name || $facebook_id_debug != $facebook_id)
    {
      $retArray['token_invalid'] = true;
      $retArray['reason'] = 'Token data does not match application data';
      $retArray['statusCode'] = StatusCode::OK;

      return JsonResponse::create($retArray);
    }

    if (!$is_valid)
    {
      $retArray['token_invalid'] = true;
      $retArray['reason'] = 'Token has been invalidated';
      $retArray['statusCode'] = StatusCode::OK;

      return JsonResponse::create($retArray);
    }

    $current_timestamp = new DateTime();
    $time_to_expiry = $current_timestamp->diff($expires);
    $limit = 3; // 3 days

    if ($time_to_expiry->m == 0 && $time_to_expiry->d < $limit)
    {
      $retArray['token_invalid'] = true;
      $retArray['statusCode'] = StatusCode::OK;
      $retArray['reason'] = 'Token will expire soon or has been expired';
      $retArray['details'] = 'Token expires at: ' . $expires->format('Y-m-d H:i:s') .
        ', current timestamp: ' . $current_timestamp->format('Y-m-d H:i:s') .
        ', time to expiry (expires - current timestamp): ' .
        $time_to_expiry->format('%R %M months, %D days, %H hours, %I minutes, %S seconds') .
        ', expiration limit: ' . $limit;

      return JsonResponse::create($retArray);
    }
    $retArray['token_invalid'] = false;
    $retArray['statusCode'] = StatusCode::OK;

    return JsonResponse::create($retArray);
  }

  /**
   * @param $token_to_check
   *
   * @return \Facebook\GraphNodes\GraphNode
   * @throws FacebookSDKException
   */
  private function checkFacebookServerAccessTokenValidity($token_to_check)
  {
    $app_token = $this->getFacebookAppToken();
    $this->initializeFacebook();

    /**
     * @var $response FacebookResponse
     */
    $response = $this->facebook->get('/debug_token?input_token=' . $token_to_check, $app_token);

    return $response->getGraphNode();
  }

  /**
   * @param null $client_token
   *
   * @throws FacebookSDKException
   */
  private function setFacebookDefaultAccessToken($client_token = null)
  {
    $this->initializeFacebook();

    if ($client_token)
    {
      $this->facebook->setDefaultAccessToken($client_token);
    }
    else
    {
      $helper = $this->facebook->getJavaScriptHelper();
      try
      {
        $accessToken = $helper->getAccessToken();
      } catch (FacebookResponseException $e)
      {
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
      } catch (FacebookSDKException $e)
      {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
      }

      if (isset($accessToken))
      {
        $this->facebook->setDefaultAccessToken($accessToken);
      }
    }
  }

  /**
   * @param $userManager
   * @param $request
   * @param $retArray
   * @param $user
   * @param $facebookId
   * @param $facebookUsername
   * @param $locale
   *
   * @throws \Exception
   */
  private function connectFacebookUserToExistingUserAccount($userManager, $request, &$retArray, $user, $facebookId, $facebookUsername, $locale)
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
        $user->setUsername($facebookUsername);
      }

      if ($user->getCountry() == '')
      {
        $user->setCountry($locale);
      }

      $user->setFacebookUid($facebookId);

      $user->setEnabled(true);
      $userManager->updateUser($user);
      $retArray['statusCode'] = 201;
      $retArray['answer'] = $this->trans("success.registration");
    }
  }

  /**
   * @param      $request
   * @param      $userManager
   * @param      $retArray
   * @param      $facebookId
   * @param      $facebookUsername
   * @param      $facebookEmail
   * @param      $locale
   * @param null $access_token
   * @throws \Exception
   */
  private function registerFacebookUser($request, $userManager, &$retArray, $facebookId, $facebookUsername, $facebookEmail, $locale, $access_token = null)
  {
    /**
     * @var $userManager UserManager
     * @var $user        User
     */
    $violations = $this->validateOAuthUser($request, $retArray);
    if (count($violations) == 0)
    {
      $user = $userManager->createUser();
      $user->setUsername($facebookUsername);
      $user->setCountry($locale);

      if ($access_token)
      {
        $user->setFacebookAccessToken($access_token);
      }

      $user->setFacebookUid($facebookId);
      $user->setEmail($facebookEmail);
      $password = $this->generateOAuthPassword($user);
      $user->setPlainPassword($password);

      $user->setEnabled(true);
      $userManager->updateUser($user);
      $retArray['statusCode'] = 201;
      $retArray['answer'] = $this->trans("success.registration");
    }
    else
    {
      $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
      $retArray['answer'] = $this->trans("errors.login");
    }
  }

  /**
   * @return string
   */
  private function getFacebookAppToken()
  {
    $app_id = $this->container->getParameter('facebook_app_id');
    $client_secret = $this->container->getParameter('facebook_secret');
    $app_token = $app_id . '|' . $client_secret;

    return $app_token;
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Exception
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
   * @throws \Exception
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
   * @throws \Exception
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
   * @throws \Exception
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
      $plus = new \Google_Service_Plus($client);
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
   * @throws \Exception
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
   * @param      $request
   * @param      $userManager
   * @param      $retArray
   * @param      $googleId
   * @param      $googleUsername
   * @param      $googleEmail
   * @param      $locale
   * @param null $access_token
   * @param null $refresh_token
   * @param null $id_token
   * @throws \Exception
   */
  private function registerGoogleUser($request, $userManager, &$retArray, $googleId, $googleUsername, $googleEmail, $locale, $access_token = null, $refresh_token = null, $id_token = null)
  {
    /**
     * @var $userManager UserManager
     * @var $user        User
     */
    $violations = $this->validateOAuthUser($request, $retArray);
    $retArray['violations'] = count($violations);
    if (count($violations) == 0)
    {
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
   * @throws \Exception
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
   * @throws \Exception
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
    if ($request->request->has('fb_id'))
    {
      $id = $request->request->get('fb_id');
      $retArray['fb_id'] = $id;
      $user = $userManager->findUserBy([
        'facebookUid' => $id,
      ]);
    }
    else
    {
      if ($request->request->has('gplus_id'))
      {
        $id = $request->request->get('gplus_id');
        $retArray['g_id'] = $id;
        $user = $userManager->findUserBy([
          'gplusUid' => $id,
        ]);
      }
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

    $retArray['error'] = 'Facebook or Google+ User not found!';

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
   * @throws \Exception
   */
  private function validateOAuthUser($request, &$retArray)
  {
    /**
     * @var $validator Validator
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
   * @throws \Exception
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

    $facebook_testuser_mail = $this->container->getParameter('facebook_testuser_mail');
    $google_testuser_mail = $this->container->getParameter('google_testuser_mail');
    $facebook_testuser_username = $this->container->getParameter('facebook_testuser_name');
    $google_testuser_username = $this->container->getParameter('google_testuser_name');
    $facebook_testuser_id = $this->container->getParameter('facebook_testuser_id');
    $google_testuser_id = $this->container->getParameter('google_testuser_id');

    $user = $userManager->findUserByEmail($facebook_testuser_mail);
    if ($user != null)
    {
      $deleted = $deleted . '_FB-Mail:' . $user->getEmail();
      $this->revokeFacebookPermissions($user, $retArray);
      $this->deleteUser($user);
    }

    $user = $userManager->findUserByEmail($google_testuser_mail);
    if ($user != null)
    {
      $deleted = $deleted . '_G+-Mail:' . $user->getEmail();
      $this->deleteUser($user);
    }

    $user = $userManager->findUserByUsername($facebook_testuser_username);
    if ($user != null)
    {
      $deleted = $deleted . '_FB-User:' . $user->getUsername();
      $this->revokeFacebookPermissions($user, $retArray);
      $this->deleteUser($user);
    }

    $user = $userManager->findUserByUsername($google_testuser_username);
    if ($user != null)
    {
      $deleted = $deleted . '_G+-User' . $user->getUsername();
      $this->deleteUser($user);
    }

    $user = $userManager->findUserBy([
      'facebookUid' => $facebook_testuser_id,
    ]);
    if ($user != null)
    {
      $deleted = $deleted . '_FB-ID:' . $user->getFacebookUid();
      $this->revokeFacebookPermissions($user, $retArray);
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
   * @param $retArray
   *
   * @throws FacebookSDKException
   */
  private function revokeFacebookPermissions($user, &$retArray)
  {
    /**
     * @var $facebook Facebook
     * @var $user     User
     */
    $this->initializeFacebook();
    $facebook = $this->facebook;

    $this->setFacebookDefaultAccessToken($user->getFacebookAccessToken());
    $response = $facebook->delete('/' . $user->getFacebookUid() . '/permissions')->getBody();
    $retArray['response'] = $response;
  }

  /**
   * @param $user
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  private function deleteUser($user)
  {
    /**
     * @var $userManager UserManager
     * @var $user        User
     * @var $em          EntityManager
     */
    $userManager = $this->container->get('usermanager');
    $program_manager = $this->container->get('programmanager');
    $em = $this->container->get('doctrine.orm.entity_manager');

    $user_programms = $program_manager->getUserPrograms($user->getId());

    foreach ($user_programms as $user_program)
    {
      $em->remove($user_program);
      $em->flush();
    }

    $userManager->deleteUser($user);
  }

  /**
   * @param       $message
   * @param array $parameters
   *
   * @return string
   * @throws \Exception
   */
  private function trans($message, $parameters = [])
  {
    return $this->container->get('translator')->trans($message, $parameters, 'catroweb');
  }
}
