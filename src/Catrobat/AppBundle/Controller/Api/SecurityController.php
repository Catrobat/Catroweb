<?php

namespace Catrobat\AppBundle\Controller\Api;

use Facebook\FacebookJavaScriptLoginHelper;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookRedirectLoginHelper;
use Google_Client;
use Google_Http_Request;
use Assetic\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Services\TokenGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\AppBundle\StatusCode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\AppBundle\Requests\CreateUserRequest;
use Catrobat\AppBundle\Requests\CreateOAuthUserRequest;
use Symfony\Component\Security\Core\Util\SecureRandom;

class SecurityController extends Controller
{
    /**
   * @Route("/api/checkToken/check.json", name="catrobat_api_check_token", defaults={"_format": "json"})
   * @Method({"POST"})
   */
  public function checkTokenAction()
  {
      return JsonResponse::create(array('statusCode' => StatusCode::OK, 'answer' => $this->trans('success.token'), 'preHeaderMessages' => "  \n"));
  }

  /**
   * @Route("/api/loginOrRegister/loginOrRegister.json", name="catrobat_api_login_or_register", defaults={"_format": "json"})
   * @Method({"POST"})
   */
  public function loginOrRegisterAction(Request $request)
  {
      $userManager = $this->get('usermanager');
      $tokenGenerator = $this->get('tokengenerator');
      $validator = $this->get('validator');

      $retArray = array();
      $username = $request->request->get('registrationUsername');

      $user = $userManager->findUserByUsername($username);

      if ($user == null) {
          $create_request = new CreateUserRequest($request);
          $violations = $validator->validate($create_request);
          foreach ($violations as $violation) {
              $retArray['statusCode'] = StatusCode::REGISTRATION_ERROR;
              switch ($violation->getMessageTemplate()) {
            case 'errors.password.short':
                $retArray['statusCode'] = StatusCode::USER_PASSWORD_TOO_SHORT;
                break;
            case 'errors.email.invalid':
                $retArray['statusCode'] = StatusCode::USER_EMAIL_INVALID;
                break;
        }
              $retArray['answer'] = $this->trans($violation->getMessageTemplate(), $violation->getParameters());
              break;
          }

          if (count($violations) == 0) {
              if ($userManager->findUserByEmail($create_request->mail) != null) {
                  $retArray['statusCode'] = StatusCode::USER_ADD_EMAIL_EXISTS;
                  $retArray['answer'] = $this->trans('errors.email.exists');
              } else {
                  $user = $userManager->createUser();
                  $user->setUsername($create_request->username);
                  $user->setEmail($create_request->mail);
                  $user->setPlainPassword($create_request->password);
                  $user->setEnabled(true);
                  $user->setUploadToken($tokenGenerator->generateToken());
                  $user->setCountry($create_request->country);

                  $userManager->updateUser($user);
                  $retArray['statusCode'] = 201;
                  $retArray['answer'] = $this->trans('success.registration');
                  $retArray['token'] = $user->getUploadToken();
              }
          }
      } else {
          $retArray['statusCode'] = StatusCode::OK;
          $correct_pass = $userManager->isPasswordValid($user, $request->request->get('registrationPassword'));
          if ($correct_pass) {
              $retArray['statusCode'] = StatusCode::OK;
              $retArray['token'] = $user->getUploadToken();
          } else {
              $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
              $retArray['answer'] = $this->trans('errors.login');
          }
      }
      $retArray['preHeaderMessages'] = '';

      return JsonResponse::create($retArray);
  }

    /**
     * @Route("/api/register/Register.json", name="catrobat_api_register", defaults={"_format": "json"})
     * @Method({"POST"})
     */
    private function registerNativeUser($request, $validator, $userManager, $tokenGenerator, &$retArray)
    {
        $userManager = $this->get("usermanager");
        $tokenGenerator = $this->get("tokengenerator");
        $validator = $this->get("validator");

        $retArray = array();
        $username = $request->request->get('registrationUsername');
        $user = $userManager->findUserByUsername($username);

        if($user == null) {
            $create_request = new CreateUserRequest($request);
            $violations = $validator->validate($create_request);
            foreach ($violations as $violation) {
                $retArray['statusCode'] = StatusCode::REGISTRATION_ERROR;
                $retArray['answer'] = $this->trans($violation->getMessageTemplate(), $violation->getParameters());
                break;
            }

            if (count($violations) == 0) {
                if ($userManager->findUserByEmail($create_request->mail) != null) {
                    $retArray['statusCode'] = StatusCode::EMAIL_ALREADY_EXISTS;
                    $retArray['answer'] = $this->trans("error.email.exists");
                } else {
                    $user = $userManager->createUser();
                    $user->setUsername($create_request->username);
                    $user->setEmail($create_request->mail);
                    $user->setPlainPassword($create_request->password);
                    $user->setEnabled(true);
                    $user->setUploadToken($tokenGenerator->generateToken());
                    $user->setCountry($create_request->country);

                    $userManager->updateUser($user);
                    $retArray['statusCode'] = 201;
                    $retArray['answer'] = $this->trans("success.registration");
                    $retArray['token'] = $user->getUploadToken();
                }
            }
        }
        $retArray['preHeaderMessages'] = "";
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/login/Login.json", name="catrobat_api_login", defaults={"_format": "json"})
     * @Method({"POST"})
     */
    private function loginNativeUser($request, $userManager, $user, &$retArray)
    {
        $userManager = $this->get("usermanager");
        $retArray = array();
        $username = $request->request->get('registrationUsername');

        $user = $userManager->findUserByUsername($username);

        $correct_pass = $userManager->isPasswordValid($user, $request->request->get('registrationPassword'));
        if ($correct_pass) {
            $retArray['statusCode'] = StatusCode::OK;
            $retArray['token'] = $user->getUploadToken();
        } else {
            $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
            $retArray['answer'] = $this->trans("error.login");
        }
        $retArray['preHeaderMessages'] = "";
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/GoogleServerTokenAvailable/GoogleServerTokenAvailable.json", name="catrobat_oauth_login_google_servertoken_available", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function checkGoogleServerTokenAvailable(Request $request)
    {
        $google_id = $request->request->get('id');

        $userManager = $this->get("usermanager");
        $retArray = array();

        $google_user = $userManager->findOneBy(array('gplusUid' => $google_id));
        if ($google_user && $google_user->getGplusAccessToken()) {
            $retArray['token_available'] = true;
            $retArray['username'] = $google_user->getUsername();
            $retArray['email'] = $google_user->getEmail();
        } else {
            $retArray['token_available'] = false;
        }
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/FacebookServerTokenAvailable/FacebookServerTokenAvailable.json", name="catrobat_oauth_login_facebook_servertoken_available", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function checkFacebookServerTokenAvailable(Request $request)
    {
        $facebook_id = $request->request->get('id');

        $userManager = $this->get("usermanager");
        $retArray = array();

        $facebook_user = $userManager->findOneBy(array('facebookUid' => $facebook_id));
        if ($facebook_user && $facebook_user->getFacebookAccessToken()) {
            $retArray['token_available'] = true;
            $retArray['username'] = $facebook_user->getUsername();
            $retArray['email'] = $facebook_user->getEmail();
        } else {
            $retArray['token_available'] = false;
        }
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/exchangeGoogleCode/exchangeGoogleCode.json", name="catrobat_oauth_login_google_code", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function exchangeGoogleCodeAction(Request $request)
    {
        $retArray = array();
        $session = $request->getSession();
        $code = $request->request->get('code');
        $sessionState = $session->get('_csrf/authenticate');
        $requestState = $request->request->get('state');

        $gPlusId = $request->request->get('id');
        $google_username = $request->request->get('username');
        $google_mail = $request->request->get('mail');
        $locale = $request->request->get('locale');

        // Ensure that this is no request forgery going on, and that the user
        // sending us this request is the user that was supposed to.
        if (!$sessionState || !$requestState || $sessionState != $requestState) {
            //return new Response('Invalid state parameter', 401);
            $retArray['sessionWarning'] = 'Warning: Invalid state parameter - This might be a Session Hijacking attempt!';
        }

        $application_name = $this->container->getParameter('application_name');
        $client_id = $this->container->getParameter('google_app_id');
        $client_secret = $this->container->getParameter('google_secret');
        $redirect_uri = 'postmessage';

        if(!$client_secret || !$client_id || !$application_name){
            throw $this->createNotFoundException('Google app authentication data not found!');
        }

        $client = new Google_Client();
        $client->setApplicationName($application_name);
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setRedirectUri($redirect_uri);
        $client->setScopes('https://www.googleapis.com/auth/userinfo.email');

        $token = '';
        if ($code) {
            $client->authenticate($code);
            $token = json_decode($client->getAccessToken());
        }

        if (!$token) {
            return new Response(
                "Google Authentication failed.", 401);
        }

        $reqUrl = 'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=' .
            $token->access_token;
        $req = new Google_Http_Request($reqUrl);

        $results = $client->execute($req);

        // Make sure the token we got is for the intended user.
        if ($results['user_id'] != $gPlusId) {
            return new Response(
                "Token's user ID doesn't match given user ID", 401);
        }

        // Make sure the token we got is for our app.
        if ($results['audience'] != $client_id) {
            return new Response(
                "Token's client ID does not match app's.", 401);
        }

        $access_token = $token->access_token;
        $id_token = $token->id_token;
        $refresh_token = '';
        if(property_exists($token, 'refresh_token')) {
            $refresh_token = $token->refresh_token;
        }

        // Store the token in the session for later use.
        $response = 'Succesfully connected with token: ' . print_r($token, true);
        $retArray['response'] = $response;

        $userManager = $this->get("usermanager");
        $user = $userManager->findUserByEmail($google_mail);
        $google_user = $userManager->findUserBy(array('gplusUid' => $gPlusId));
        if ($google_user) {
            $this->setGoogleTokens($userManager, $google_user, $access_token, $refresh_token, $id_token);
        } else if ($user) {
            $this->connectGoogleUserToExistingUserAccount($userManager, $request, $retArray, $user, $gPlusId, $google_username);
            $this->setGoogleTokens($userManager, $user, $access_token, $refresh_token, $id_token);
        } else {
            $this->registerGoogleUser($request, $userManager, $retArray, $gPlusId, $google_username, $google_mail, $locale, $access_token, $refresh_token, $id_token);
        }

        $retArray['statusCode'] = 201;
        $retArray['answer'] = $this->trans("success.registration");
        return JsonResponse::create($retArray);
    }


    /**
     * @Route("/api/exchangeFacebookToken/exchangeFacebookToken.json", name="catrobat_oauth_login_facebook_token", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function exchangeFacebookTokenAction(Request $request)
    {
        $retArray = array();
        $session = $request->getSession();
        $client_token = $request->request->get('client_token');
        $sessionState = $session->get('_csrf/authenticate');
        $requestState = $request->request->get('state');

        $facebookId = $request->request->get('id');
        $facebook_username = $request->request->get('username');
        $facebook_mail = $request->request->get('mail');
        $locale = $request->request->get('locale');

        // Ensure that this is no request forgery going on, and that the user
        // sending us this request is the user that was supposed to.
        if (!$sessionState || !$requestState || $sessionState != $requestState) {
            //return new Response('Invalid state parameter', 401);
            $retArray['sessionWarning'] = 'Warning: Invalid state parameter - This might be a Session Hijacking attempt!';
        }

        $application_name = $this->container->getParameter('application_name');
        $app_id = $this->container->getParameter('facebook_app_id');
        $client_secret = $this->container->getParameter('facebook_secret');
        $app_token = $app_id . '|' . $client_secret;

        if(!$client_secret || !$app_id || !$application_name) {
            throw $this->createNotFoundException('Facebook app authentication data not found!');
        }

        FacebookSession::setDefaultApplication($app_id, $client_secret);

        $helper = new FacebookJavaScriptLoginHelper();
        try {
            $facebook_session = $helper->getSession();
        } catch (FacebookRequestException $ex) {
            // When Facebook returns an error
            return new Response(
                "Facebook Session could not be retrieved", 401);
        } catch (\Exception $ex) {
            // When validation fails or other local issues
            return new Response(
                "Facebook Session could not be retrieved", 401);
        }

        try {
            $result = (new FacebookRequest($facebook_session, 'GET', '/oauth/access_token', array('grant_type' => 'fb_exchange_token',
                'client_id' => $app_id, 'client_secret' => $client_secret, 'fb_exchange_token' => $client_token)))->execute()->getGraphObject();
            $server_token = $result->getProperty('access_token');
        } catch (FacebookRequestException $exception) {
            return new Response(
                "Graph API returned an error during token exchange", 401);
        } catch (\Exception $exception) {
            return new Response(
                "Error during token exchange", 401);
        }

        try {
            $result = (new FacebookRequest($facebook_session, 'GET', '/debug_token', array('input_token' => $server_token,
                'access_token' => $app_token)))->execute()->getGraphObject();
            $app_id_debug = $result->getProperty('app_id');
            $application_name_debug = $result->getProperty('application');
            $facebookId_debug = $result->getProperty('user_id');
        } catch (FacebookRequestException $exception) {
            return new Response(
                "Graph API returned an error during token exchange", 401);
        } catch (\Exception $exception) {
            return new Response(
                "Error during token exchange", 401);
        }

        // Make sure the token we got is for the intended user.
        if ($facebookId_debug != $facebookId) {
            return new Response(
                "Token's user ID doesn't match given user ID", 401);
        }

        // Make sure the token we got is for our app.
        if ($app_id_debug != $app_id || $application_name_debug != $application_name) {
            return new Response(
                "Token's client ID or app name does not match app's.", 401);
        }

        $userManager = $this->get("usermanager");
        $user = $userManager->findUserByEmail($facebook_mail);
        $facebook_user = $userManager->findUserBy(array('facebookUid' => $facebookId));
        if ($facebook_user) {
            $facebook_user->setFacebookAccessToken($server_token);
        } else if ($user) {
            $this->connectFacebookUserToExistingUserAccount($userManager, $request, $retArray, $user, $facebookId, $facebook_username);
            $user->setFacebookAccessToken($server_token);
        } else {
            $this->registerFacebookUser($request, $userManager, $retArray, $facebookId, $facebook_username, $facebook_mail, $locale, $server_token);
        }

        if(!$retArray['statusCode'] == StatusCode::LOGIN_ERROR){
            $retArray['statusCode'] = 201;
            $retArray['answer'] = $this->trans("success.registration");
        }

        return JsonResponse::create($retArray);
    }


    private function setGoogleTokens($userManager, $user, $access_token, $refresh_token, $id_token)
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
        $userManager->updateUser($user);
    }

    /**
     * @Route("/api/loginWithGoogle/loginWithGoogle.json", name="catrobat_oauth_login_google", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function loginWithGoogleAction(Request $request)
    {
        $userManager = $this->get("usermanager");
        $retArray = array();

        $google_username = $request->request->get('username');
        $google_id = $request->request->get('id');
        $google_mail = $request->request->get('mail');

        $user = $userManager->findUserByEmail($google_mail);
        $google_user = $userManager->findOneBy(array('gplusUid' => $google_id));
        if ($google_user) {
            $retArray['password'] = $google_user->getOauthPassword();
            $this->loginOAuthUser($retArray);
        } else if ($user) {
            $this->connectGoogleUserToExistingUserAccount($userManager, $request, $retArray, $user, $google_id, $google_username);
            $retArray['password'] = $user->getOauthPassword();
        }

        $retArray['username'] = $google_username;
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/loginWithFacebook/loginWithFacebook.json", name="catrobat_oauth_login_facebook", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function loginWithFacebookAction(Request $request)
    {
        $userManager = $this->get("usermanager");
        $retArray = array();

        $fb_username = $request->request->get('username');
        $fb_id = $request->request->get('id');
        $fb_mail = $request->request->get('mail');

        $user = $userManager->findUserByEmail($fb_mail);
        $fb_user = $userManager->findOneBy(array('facebookUid' => $fb_id));
        if ($fb_user) {
            $this->loginOAuthUser($retArray);
            $retArray['password'] = $fb_user->getOauthPassword();
        } else if ($user) {
            $this->connectFacebookUserToExistingUserAccount($userManager, $request, $retArray, $user, $fb_id, $fb_username, $fb_mail);
            $retArray['password'] = $user->getOauthPassword();
        }

        $retArray['username'] = $fb_username;
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/getFacebookAppId/getFacebookAppId.json", name="catrobat_oauth_login_get_facebook_appid", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function getFacebookAppId()
    {
        $retArray = array();
        $retArray['fb_appid'] = $this->container->getParameter('facebook_app_id');
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/getGoogleAppId/getGoogleAppId.json", name="catrobat_oauth_login_get_google_appid", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function getGoogleAppId()
    {
        $retArray = array();
        $retArray['gplus_appid'] = $this->container->getParameter('google_app_id');
        return JsonResponse::create($retArray);
    }

    private function validateOAuthUser($request, &$retArray)
    {
        $validator = $this->get("validator");
        $create_request = new CreateOAuthUserRequest($request);
        $violations = $validator->validate($create_request);
        foreach ($violations as $violation) {
            $retArray['statusCode'] = StatusCode::REGISTRATION_ERROR;
            $retArray['answer'] = $this->trans($violation->getMessageTemplate(), $violation->getParameters());
            break;
        }
        return $violations;
    }

    private function connectGoogleUserToExistingUserAccount($userManager, $request, &$retArray, $user, $googleId, $googleUsername)
    {
        $violations = $this->validateOAuthUser($request, $retArray);
        if (count($violations) == 0) {
            if ($user->getUsername() == '') {
                $user->setUsername($googleUsername);
            }
            $user->setGplusName($googleUsername);
            $user->setGplusUid($googleId);

            $user->setEnabled(true);
            $userManager->updateUser($user);
            $retArray['statusCode'] = 201;
            $retArray['answer'] = $this->trans("success.registration");
        }
    }

    private function connectFacebookUserToExistingUserAccount($userManager, $request, &$retArray, $user, $facebookId, $facebookUsername)
    {
        $violations = $this->validateOAuthUser($request, $retArray);
        if (count($violations) == 0) {
            if ($user->getUsername() == '') {
                $user->setUsername($facebookUsername);
            }
            $user->setFacebookName($facebookUsername);
            $user->setFacebookUid($facebookId);

            $user->setEnabled(true);
            $userManager->updateUser($user);
            $retArray['statusCode'] = 201;
            $retArray['answer'] = $this->trans("success.registration");
        }
    }

    private function registerFacebookUser($request, $userManager, &$retArray, $facebookId, $facebookUsername, $facebookEmail, $locale, $access_token = null)
    {
        $violations = $this->validateOAuthUser($request, $retArray);
        if (count($violations) == 0) {
            $user = $userManager->createUser();
            $user->setUsername($facebookUsername);
            $user->setCountry($locale);

            if ($access_token) {
                $user->setFacebookAccessToken($access_token);
            }

            $user->setFacebookUid($facebookId);
            $user->setEmail($facebookEmail);

            $generator = new SecureRandom();
            $password = bin2hex($generator->nextBytes(16));

            $retArray['password'] = $password;
            $user->setPlainPassword($password);
            $user->setOauthPassword($password);

            $user->setEnabled(true);
            $userManager->updateUser($user);
            $retArray['statusCode'] = 201;
            $retArray['answer'] = $this->trans("success.registration");
        } else {
            $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
            $retArray['answer'] = $this->trans("error.login");
        }
    }

    private function registerGoogleUser($request, $userManager, &$retArray, $googleId, $googleUsername, $googleEmail,
                                        $locale, $access_token = null, $refresh_token = null, $id_token = null)
    {
        $violations = $this->validateOAuthUser($request, $retArray);
        $retArray['violations'] = count($violations);
        if (count($violations) == 0) {
            $user = $userManager->createUser();
            $user->setUsername($googleUsername);
            //$user->setGplusName($googleUsername);
            $user->setGplusUid($googleId);
            $user->setEmail($googleEmail);
            $user->setCountry($locale);
            $generator = new SecureRandom();
            $password = bin2hex($generator->nextBytes(16));
            $retArray['password'] = $password;

            $user->setPlainPassword($password);
            $user->setOauthPassword($password);
            if ($access_token) {
                $user->setGplusAccessToken($access_token);
            }
            if ($refresh_token) {
                $user->setGplusRefreshToken($refresh_token);
            }
            if ($id_token) {
                $user->setGplusIdToken($id_token);
            }

            $user->setEnabled(true);
            $userManager->updateUser($user);
            $retArray['statusCode'] = 201;
            $retArray['answer'] = $this->trans("success.registration");
        }
    }

    private function loginOAuthUser(&$retArray)
    {
        $retArray['statusCode'] = StatusCode::OK;
    }

    private function trans($message, $parameters = array())
    {
        return $this->get('translator')->trans($message, $parameters, 'catroweb');
    }

}

