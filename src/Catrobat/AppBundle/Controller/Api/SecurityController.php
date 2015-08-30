<?php

namespace Catrobat\AppBundle\Controller\Api;

use Facebook\FacebookJavaScriptLoginHelper;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookRedirectLoginHelper;
use Google_Client;
use Google_Http_Request;
use Google_Service_Plus;
use Assetic\Exception;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use HWI\Bundle\OAuthBundle\Tests\Security\Core\Authentication\Token\OAuthTokenTest;
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
use Catrobat\AppBundle\Requests\LoginUserRequest;
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


    /*
     * loginOrRegisterAction is DEPRECATED!!
     */
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
     * @Route("/api/register/Register.json", name="catrobat_api_register", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function registerNativeUser(Request $request)
    {
        $userManager = $this->get("usermanager");
        $tokenGenerator = $this->get("tokengenerator");
        $validator = $this->get("validator");

        $retArray = array();

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
                $retArray['answer'] = $this->trans("error.email.exists");
            } else if ($userManager->findUserByUsername($create_request->username) != null) {
                $retArray['statusCode'] = StatusCode::USER_ADD_USERNAME_EXISTS;
                $retArray['answer'] = $this->trans("error.username.exists");
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
        $retArray['preHeaderMessages'] = "";
        return JsonResponse::create($retArray);
    }


    /**
     * @Route("/api/login/Login.json", name="catrobat_api_login", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function loginNativeUser(Request $request)
    {
        $userManager = $this->get("usermanager");
        $validator = $this->get("validator");
        $tokenGenerator = $this->get("tokengenerator");
        $retArray = array();

        $login_request = new LoginUserRequest($request);
        $violations = $validator->validate($login_request);
        foreach ($violations as $violation) {
            $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
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
            $username = $request->request->get('registrationUsername');
            $password = $request->request->get('registrationPassword');

            $user = $userManager->findUserByUsername($username);

            if(!$user) {
                $retArray['statusCode'] = StatusCode::USER_USERNAME_INVALID;
                $retArray['answer'] = $this->trans('errors.username.not_exists');
            } else {
                $correct_pass = $userManager->isPasswordValid($user, $password);
                if ($correct_pass) {
                    $retArray['statusCode'] = StatusCode::OK;
                    $user->setUploadToken($tokenGenerator->generateToken());
                    $retArray['token'] = $user->getUploadToken();
                    $retArray['email'] = $user->getEmail();
                    $userManager->updateUser($user);
                } else {
                    $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
                    $retArray['answer'] = $this->trans("error.login");
                }
            }
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
        $retArray['statusCode'] = StatusCode::OK;
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
        $retArray['statusCode'] = StatusCode::OK;
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/EMailAvailable/EMailnAvailable.json", name="catrobat_oauth_login_email_available", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function checkEMailAvailable(Request $request)
    {
        $email = $request->request->get('email');

        $userManager = $this->get("usermanager");
        $retArray = array();

        $user = $userManager->findOneBy(array('email' => $email));
        if ($user) {
            $retArray['email_available'] = true;
            $retArray['username'] = $user->getUsername();
        } else {
            $retArray['email_available'] = false;
        }
        $retArray['statusCode'] = StatusCode::OK;
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/UsernameAvailable/UsernameAvailable.json", name="catrobat_oauth_login_username_available", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function checkUserNameAvailable(Request $request)
    {
        $username = $request->request->get('username');

        $userManager = $this->get("usermanager");
        $retArray = array();

        $user = $userManager->findOneBy(array('username' => $username));

        if ($user) {
            $retArray['username_available'] = true;
        } else {
            $retArray['username_available'] = false;
        }
        $retArray['statusCode'] = StatusCode::OK;
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/IsOAuthUser/IsOAuthUser.json", name="catrobat_is_oauth_user", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function isOAuthUser(Request $request)
    {
        $username_email = $request->request->get('username_email');

        $userManager = $this->get("usermanager");
        $retArray = array();

        $user = $userManager->findOneBy(array('username' => $username_email));
        if (!$user) {
            $user = $userManager->findOneBy(array('email' => $username_email));
        }

        if ($user && ($user->getFacebookUid() || $user->getGplusUid())) {
            $retArray['is_oauth_user'] = true;
        } else {
            $retArray['is_oauth_user'] = false;
        }
        $retArray['statusCode'] = StatusCode::OK;
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
        $google_mail = $request->request->get('email');
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

        if (!$client_secret || !$client_id || !$application_name) {
            throw $this->createNotFoundException('Google app authentication data not found!');
        }

        $client = new Google_Client();
        $client->setApplicationName($application_name);
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        if (!$request->request->has('mobile')) {
            $client->setRedirectUri($redirect_uri);
        }
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
        if (property_exists($token, 'refresh_token')) {
            $refresh_token = $token->refresh_token;
        }

        // Store the token in the session for later use.
        //'Succesfully connected with token: ' . print_r($token, true);

        $userManager = $this->get("usermanager");
        $user = $userManager->findUserByEmail($google_mail);
        $google_user = $userManager->findUserBy(array('gplusUid' => $gPlusId));
        if ($google_user) {
            $this->setGoogleTokens($userManager, $google_user, $access_token, $refresh_token, $id_token);
        } else if ($user) {
            $this->connectGoogleUserToExistingUserAccount($userManager, $request, $retArray, $user, $gPlusId, $google_username, $locale);
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
        $facebook_mail = $request->request->get('email');
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

        if (!$client_secret || !$app_id || !$application_name) {
            throw $this->createNotFoundException('Facebook app authentication data not found!');
        }

        if ($request->request->has('mobile')) {
            $facebook_session = $this->getFacebookSession($client_token);
        } else {
            $facebook_session = $this->getFacebookSession();
        }

        try {
            $result = (new FacebookRequest($facebook_session, 'GET', '/oauth/access_token', array('grant_type' => 'fb_exchange_token',
                'client_id' => $app_id, 'client_secret' => $client_secret, 'fb_exchange_token' => $client_token)))->execute()->getGraphObject();
            $server_token = $result->getProperty('access_token');
        } catch (FacebookRequestException $exception) {
            return new Response(
                "Graph API returned an error during token exchange for 'GET', '/oauth/access_token'", 401);
        } catch (\Exception $exception) {
            return new Response(
                "Error during token exchange for 'GET', '/oauth/access_token' with exception" . $exception, 401);
        }

        try {
            $result = $this->checkFacebookServerAccessTokenValidity($facebook_session, $server_token);
            $app_id_debug = $result->getProperty('app_id');
            $application_name_debug = $result->getProperty('application');
            $facebookId_debug = $result->getProperty('user_id');
        } catch (FacebookRequestException $exception) {
            return new Response(
                "Graph API returned an error during token exchange for 'GET', '/debug_token'", 401);
        } catch (\Exception $exception) {
            return new Response(
                "Error during token exchange for 'GET', '/debug_token' with exception" . $exception, 401);
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
            $this->connectFacebookUserToExistingUserAccount($userManager, $request, $retArray, $user, $facebookId, $facebook_username, $locale);
            $user->setFacebookAccessToken($server_token);
            $userManager->updateUser($user);
        } else {
            $this->registerFacebookUser($request, $userManager, $retArray, $facebookId, $facebook_username, $facebook_mail, $locale, $server_token);
        }

        if (!array_key_exists('statusCode', $retArray) || !$retArray['statusCode'] == StatusCode::LOGIN_ERROR) {
            $retArray['statusCode'] = 201;
            $retArray['answer'] = $this->trans("success.registration");
        }

        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/checkFacebookServerTokenValidity/checkFacebookServerTokenValidity.json", name="catrobat_oauth_facebook_server_token_validity", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function isFacebookServerAccessTokenValid(Request $request)
    {
        $userManager = $this->get("usermanager");
        $retArray = array();

        $facebook_id = $request->request->get('id');

        $fb_user = $userManager->findOneBy(array('facebookUid' => $facebook_id));
        if (!$fb_user) {
            //should not happen, but who knows
            $retArray['token_invalid'] = true;
            $retArray['reason'] = 'No Facebook User with given ID in database';
            $retArray['statusCode'] = StatusCode::OK;
            return JsonResponse::create($retArray);
        }

        $app_token = $this->getAppToken();
        $server_token_to_check = $fb_user->getFacebookAccessToken();

        $facebook_session = $this->getFacebookSession($app_token);
        $result = $this->checkFacebookServerAccessTokenValidity($facebook_session, $server_token_to_check);

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

        $application_name = $this->container->getParameter('application_name');
        $app_id = $this->container->getParameter('facebook_app_id');

        $is_valid = $result->getProperty('is_valid');
        $expires = $result->getProperty('expires_at');
        $app_id_debug = $result->getProperty('app_id');
        $application_name_debug = $result->getProperty('application');
        $facebook_id_debug = $result->getProperty('user_id');

        if ($app_id_debug != $app_id || $application_name_debug != $application_name || $facebook_id_debug != $facebook_id) {
            $retArray['token_invalid'] = true;
            $retArray['reason'] = 'Token data does not match application data';
            $retArray['statusCode'] = StatusCode::OK;
            return JsonResponse::create($retArray);
        }

        if (!$is_valid) {
            $retArray['token_invalid'] = true;
            $retArray['reason'] = 'Token has been invalidated';
            $retArray['statusCode'] = StatusCode::OK;
            return JsonResponse::create($retArray);
        }

        $current_timestamp = time();
        $time_to_expiry = $expires - $current_timestamp;
        $limit = 5 * 24 * 60 * 60; //5 days

        if ($time_to_expiry < $limit) {
            $retArray['token_invalid'] = true;
            $retArray['statusCode'] = StatusCode::OK;
            $retArray['reason'] = 'Token will expire soon or has been expired';
            return JsonResponse::create($retArray);
        }
        $retArray['token_invalid'] = false;
        $retArray['statusCode'] = StatusCode::OK;
        return JsonResponse::create($retArray);
    }

    private function checkFacebookServerAccessTokenValidity($facebook_session, $token_to_check)
    {
        $app_token = $this->getAppToken();
        return (new FacebookRequest($facebook_session, 'GET', '/debug_token', array('input_token' => $token_to_check,
            'access_token' => $app_token)))->execute()->getGraphObject();
    }

    private function getAppToken() {
        $app_id = $this->container->getParameter('facebook_app_id');
        $client_secret = $this->container->getParameter('facebook_secret');
        $app_token = $app_id . '|' . $client_secret;
        return $app_token;
    }

    /**
     * @Route("/api/loginWithGoogle/loginWithGoogle.json", name="catrobat_oauth_login_google", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function loginWithGoogleAction(Request $request)
    {
        $userManager = $this->get("usermanager");
        $tokenGenerator = $this->get('tokengenerator');
        $retArray = array();

        $google_username = $request->request->get('username');
        $google_id = $request->request->get('id');
        $google_mail = $request->request->get('email');
        $locale = $request->request->get('locale');

        $user = $userManager->findUserByEmail($google_mail);
        $google_user = $userManager->findOneBy(array('gplusUid' => $google_id));
        if ($google_user) {
            $retArray['password'] = $google_user->getOauthPassword();
            $google_user->setUploadToken($tokenGenerator->generateToken());
            $userManager->updateUser($google_user);
            $retArray['token'] = $google_user->getUploadToken();
            $retArray['username'] = $google_user->getUsername();
            $this->loginOAuthUser($retArray);
        } else if ($user) {
            $this->connectGoogleUserToExistingUserAccount($userManager, $request, $retArray, $user, $google_id, $google_username, $locale);
            $retArray['password'] = $user->getOauthPassword();
            $user->setUploadToken($tokenGenerator->generateToken());
            $userManager->updateUser($user);
            $retArray['token'] = $user->getUploadToken();
            $retArray['username'] = $user->getUsername();
        }

        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/getFacebookUserInfo/getFacebookUserInfo.json", name="catrobat_facebook_userinfo", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function getFacebookUserProfileInfo(Request $request)
    {
        $userManager = $this->get("usermanager");
        $retArray = array();

        $facebook_id = $request->request->get('id');
        $facebook_user = $userManager->findOneBy(array('facebookUid' => $facebook_id));

        if ($facebook_user) {

            if ($request->request->has('mobile')) {
                $client_token = $facebook_user->getFacebookAccessToken();
                $facebook_session = $this->getFacebookSession($client_token);
            } else {
                $facebook_session = $this->getFacebookSession();
            }

            $request = new FacebookRequest(
                $facebook_session,
                'GET',
                '/' . $facebook_id
            );
            $response = $request->execute();
            $graphObject = $response->getGraphObject();
            $retArray['id'] = $graphObject->getProperty('id');
            $retArray['first_name'] = $graphObject->getProperty('first_name');
            $retArray['last_name'] = $graphObject->getProperty('last_name');
            $retArray['name'] = $graphObject->getProperty('name');
            $retArray['link'] = $graphObject->getProperty('link');
        } else {
            $retArray['error'] = 'invalid id';
        }

        return JsonResponse::create($retArray);
    }

    private function getFacebookSession($client_token = NULL) {
        $app_id = $this->container->getParameter('facebook_app_id');
        $client_secret = $this->container->getParameter('facebook_secret');

        if (!$client_secret || !$app_id) {
            throw $this->createNotFoundException('Facebook app authentication data not found!');
        }

        FacebookSession::setDefaultApplication($app_id, $client_secret);

        if ($client_token) {
            $facebook_session = new FacebookSession($client_token);
        } else {
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
        }
        try {
            $facebook_session->validate();
        } catch (FacebookSDKException $ex) {
            //session expired or invalid
            $facebook_session = null;
        }

        return $facebook_session;
    }

    /**
     * @Route("/api/getGoogleUserInfo/getGoogleUserInfo.json", name="catrobat_google_userinfo", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function getGoogleUserProfileInfo(Request $request)
    {
        $userManager = $this->get("usermanager");
        $retArray = array();

        $google_id = $request->request->get('id');
        $google_user = $userManager->findOneBy(array('gplusUid' => $google_id));

        if ($google_user) {
            $this->refreshGoogleAccessToken($google_user);

            $client = $this->getAuthenticatedGoogleClientForGPlusUser($google_user);
            $plus = new \Google_Service_Plus($client);
            $person = $plus->people->get($google_id);

            $retArray['ID'] = $person->getId();
            $retArray['displayName'] = $person->getDisplayName();
            $retArray['imageUrl'] = $person->getImage()->getUrl();
            $retArray['profileUrl'] = $person->getUrl();
        } else {
            $retArray['error'] = 'invalid id';
        }

        return JsonResponse::create($retArray);
    }

    private function refreshGoogleAccessToken($user)
    {
        //Google offline server tokens are valid for ~1h. So, we need to check if the token has to be refreshed
        //before making server-side requests. The refresh token has an unlimited lifetime.
        $userManager = $this->get("usermanager");
        $server_access_token = $user->getGplusAccessToken();
        $refresh_token = $user->getGplusRefreshToken();

        if ($server_access_token != null && $refresh_token != null) {

            $client = $this->getAuthenticatedGoogleClientForGPlusUser($user);

            $reqUrl = 'https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=' .
                $server_access_token;
            $req = new Google_Http_Request($reqUrl);

            /* result for valid token:
                {
                 "issued_to": "[app id]",
                 "audience": "[app id]",
                 "user_id": "[user id]",
                 "scope": "https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/plus.moments.write https://www.googleapis.com/auth/plus.me https://www.googleapis.com/auth/plus.profile.agerange.read https://www.googleapis.com/auth/plus.profile.language.read https://www.googleapis.com/auth/plus.circles.members.read https://www.googleapis.com/auth/userinfo.profile",
                 "expires_in": 3181,
                 "email": "[email]",
                 "verified_email": [true/false],
                 "access_type": "offline"
                }
            result for invalid token:
                {
                 "error_description": "Invalid Value"
                }
            */

            $results = get_object_vars(json_decode($client->getAuth()->authenticatedRequest($req)->getResponseBody()));

            if (isset($results['error_description']) && $results['error_description'] == 'Invalid Value') {
                //token is expired --> refresh
                $newtoken_array = json_decode($client->getAccessToken());
                $newtoken = $newtoken_array->access_token;
                $user->setGplusAccessToken($newtoken);
                $userManager->updateUser($user);
            }
        }
    }

    private function getAuthenticatedGoogleClientForGPlusUser($user)
    {
        $application_name = $this->container->getParameter('application_name');
        $client_id = $this->container->getParameter('google_app_id');
        $client_secret = $this->container->getParameter('google_secret');
        //$redirect_uri = 'postmessage';

        if (!$client_secret || !$client_id || !$application_name) {
            throw $this->createNotFoundException('Google app authentication data not found!');
        }

        $server_access_token = $user->getGplusAccessToken();
        $refresh_token = $user->getGplusRefreshToken();

        $client = new Google_Client();
        $client->setApplicationName($application_name);
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        //$client->setRedirectUri($redirect_uri);
        $client->setScopes('https://www.googleapis.com/auth/userinfo.email');
        $client->setState('offline');
        $token_array = array();
        $token_array['access_token'] = $server_access_token;
        $client->setAccessToken(json_encode($token_array));
        $client->refreshToken($refresh_token);
        return $client;
    }

    /**
     * @Route("/api/loginWithFacebook/loginWithFacebook.json", name="catrobat_oauth_login_facebook", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function loginWithFacebookAction(Request $request)
    {
        $userManager = $this->get("usermanager");
        $tokenGenerator = $this->get('tokengenerator');
        $retArray = array();

        $fb_username = $request->request->get('username');
        $fb_id = $request->request->get('id');
        $fb_mail = $request->request->get('email');
        $locale = $request->request->get('locale');

        $user = $userManager->findUserByEmail($fb_mail);
        $fb_user = $userManager->findOneBy(array('facebookUid' => $fb_id));
        if ($fb_user) {
            $this->loginOAuthUser($retArray);
            $retArray['password'] = $fb_user->getOauthPassword();
        } else if ($user) {
            $this->connectFacebookUserToExistingUserAccount($userManager, $request, $retArray, $user, $fb_id, $fb_username, $fb_mail, $locale);
            $retArray['password'] = $user->getOauthPassword();
        }

        $user->setUploadToken($tokenGenerator->generateToken());
        $userManager->updateUser($user);
        $retArray['token'] = $user->getUploadToken();

        $retArray['username'] = $user->getUsername();
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

    private function connectGoogleUserToExistingUserAccount($userManager, $request, &$retArray, $user, $googleId, $googleUsername, $locale)
    {
        $violations = $this->validateOAuthUser($request, $retArray);
        if (count($violations) == 0) {
            if ($user->getUsername() == '') {
                $user->setUsername($googleUsername);
            }
            if ($user->getCountry() == '') {
                $user->setCountry($locale);
            }

            $user->setGplusUid($googleId);
            $retArray['password'] = $this->generateOAuthPassword($user);

            $user->setEnabled(true);
            $userManager->updateUser($user);
            $retArray['statusCode'] = 201;
            $retArray['answer'] = $this->trans("success.registration");
        }
    }

    private function connectFacebookUserToExistingUserAccount($userManager, $request, &$retArray, $user, $facebookId, $facebookUsername, $locale)
    {
        $violations = $this->validateOAuthUser($request, $retArray);
        if (count($violations) == 0) {
            if ($user->getUsername() == '') {
                $user->setUsername($facebookUsername);
            }

            if ($user->getCountry() == '') {
                $user->setCountry($locale);
            }

            $user->setFacebookUid($facebookId);
            $retArray['password'] = $this->generateOAuthPassword($user);

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
            $retArray['password'] = $this->generateOAuthPassword($user);

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
            $user->setGplusUid($googleId);
            $user->setEmail($googleEmail);
            $user->setCountry($locale);

            $retArray['password'] = $this->generateOAuthPassword($user);

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


    /**
     * @Route("/api/generateCsrfToken/generateCsrfToken.json", name="catrobat_oauth_register_get_csrftoken", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function generateCsrfToken()
    {
        $retArray = array();
        $retArray['csrf_token'] = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/deleteOAuthUserAccounts/deleteOAuthUserAccounts.json", name="catrobat_oauth_delete_testusers", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function deleteOAuthTestUserAccounts()
    {
        $userManager = $this->get('usermanager');
        $retArray = array();

        $deleted = '';

        $facebook_testuser_mail = 'pocket_zlxacqt_tester@tfbnw.net';
        $google_testuser_mail = 'pocketcodetester@gmail.com';
        $facebook_testuser_username = 'HeyWickieHey';
        $google_testuser_username = 'PocketGoogler';
        $facebook_testuser_id = '105678789764016';
        $google_testuser_id = '105155320106786463089';

        $user = $userManager->findUserByEmail($facebook_testuser_mail);
        if ($user != null) {
            $deleted = $deleted . '_FB-Mail:' . $user->getEmail();
            $this->deleteUser($user);
        }

        $user = $userManager->findUserByEmail($google_testuser_mail);
        if ($user != null) {
            $deleted = $deleted . '_G+-Mail:' . $user->getEmail();
            $this->deleteUser($user);
        }

        $user = $userManager->findUserByUsername($facebook_testuser_username);
        if ($user != null) {
            $deleted = $deleted . '_FB-User:' . $user->getUsername();
            $this->deleteUser($user);
        }

        $user = $userManager->findUserByUsername($google_testuser_username);
        if ($user != null) {
            $deleted = $deleted . '_G+-User' . $user->getUsername();
            $this->deleteUser($user);
        }

        $user = $userManager->findUserBy(array('facebookUid' => $facebook_testuser_id));
        if ($user != null) {
            $deleted = $deleted . '_FB-ID:' . $user->getFacebookUid();
            $this->deleteUser($user);
        }

        $user = $userManager->findUserBy(array('gplusUid' => $google_testuser_id));
        if ($user != null) {
            $deleted = $deleted . '_G+-ID' . $user->getGplusUid();
            $this->deleteUser($user);
        }

        $retArray['deleted'] = $deleted;
        $retArray['statusCode'] = StatusCode::OK;

        return JsonResponse::create($retArray);
    }

    private function deleteUser($user)
    {
        $userManager = $this->get('usermanager');
        $program_manager = $this->get('programmanager');
        $em = $this->getDoctrine()->getEntityManager();

        $user_programms = $program_manager->getUserPrograms($user->getId());

        foreach ($user_programms as $user_program) {
            $em->remove($user_program);
            $em->flush();
        }

        $userManager->deleteUser($user);
    }

    private function generateOAuthPassword($user)
    {
        $generator = new SecureRandom();
        $password = bin2hex($generator->nextBytes(16));
        $user->setPlainPassword($password);
        $user->setOauthPassword($password);
        return $password;
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
