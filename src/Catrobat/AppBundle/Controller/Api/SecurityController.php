<?php

namespace Catrobat\AppBundle\Controller\Api;

use Assetic\Exception;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\UserLDAPManager;
use Symfony\Component\HttpFoundation\Request;
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
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Catrobat\AppBundle\Security\UserAuthenticator;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class SecurityController extends Controller
{
    /**
     * @Route("/api/checkToken/check.json", name="catrobat_api_check_token", defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function checkTokenAction() {
        return JsonResponse::create(array(
            'statusCode' => StatusCode::OK,
            'answer' => $this->trans('success.token'),
            'preHeaderMessages' => "  \n"
        ));
    }

    /*
     * loginOrRegisterAction is DEPRECATED!!
     */
    /**
     * @Route("/api/loginOrRegister/loginOrRegister.json", name="catrobat_api_login_or_register", defaults={"_format": "json"})
     * @Method({"POST"})
     *
     * @deprecated
     *
     */
    public function loginOrRegisterAction(Request $request) {
        /**
         * @var $userManager UserManager
         * @var $user User
         */
        $userManager = $this->get('usermanager');
        $tokenGenerator = $this->get('tokengenerator');
        $validator = $this->get('validator');

        $retArray = array();

        $this->signInLdapUser($request, $retArray);
        if (array_key_exists('statusCode', $retArray) && ($retArray['statusCode'] === StatusCode::OK || $retArray['statusCode'] === StatusCode::LOGIN_ERROR)) {
            return JsonResponse::create($retArray);
        }

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

                $violations = $validator->validate($user, "Registration");
                if (count($violations) > 0) {
                    $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
                    $retArray['answer'] = $this->trans('errors.login');
                } else {
                    $userManager->updateUser($user);
                    $retArray['statusCode'] = 201;
                    $retArray['answer'] = $this->trans('success.registration');
                    $retArray['token'] = $user->getUploadToken();
                }
            }
        }
        $retArray['preHeaderMessages'] = '';
        return JsonResponse::create($retArray);

    }

    /**
     * @Route("/api/register/Register.json", name="catrobat_api_register", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function registerNativeUser(Request $request) {
        /**
         * @var $userManager UserManager
         * @var $user User
         */
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
                $retArray['answer'] = $this->trans("errors.email.exists");
            } else
                if ($userManager->findUserByUsername($create_request->username) != null) {
                    $retArray['statusCode'] = StatusCode::USER_ADD_USERNAME_EXISTS;
                    $retArray['answer'] = $this->trans("errors.username.exists");
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
    public function loginNativeUser(Request $request) {
        /**
         * @var $userManager UserManager
         * @var $user User
         */

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

        if (count($violations) > 0) {
            $retArray['preHeaderMessages'] = "";
            return JsonResponse::create($retArray);
        }

        if (count($violations) == 0) {
            $username = $request->request->get('registrationUsername');
            $password = $request->request->get('registrationPassword');

            $user = $userManager->findUserByUsername($username);

            if (!$user) {
                $this->signInLdapUser($request, $retArray);
                if (array_key_exists('statusCode', $retArray) && ($retArray['statusCode'] === StatusCode::OK || $retArray['statusCode'] === StatusCode::LOGIN_ERROR)) {
                    return JsonResponse::create($retArray);
                }
                $retArray['statusCode'] = StatusCode::USER_USERNAME_INVALID;
                $retArray['answer'] = $this->trans('errors.username.not_exists');
            } else {
                $correct_pass = $userManager->isPasswordValid($user, $password);
                if ($correct_pass) {
                    $retArray['statusCode'] = StatusCode::OK;
                    $user->setUploadToken($tokenGenerator->generateToken());
                    $retArray['token'] = $user->getUploadToken();
                    $retArray['email'] = $user->getEmail();
                    $retArray['nolbUser'] = $user->getNolbUser();
                    $userManager->updateUser($user);
                } else {
                    $this->signInLdapUser($request, $retArray);
                    if (array_key_exists('statusCode', $retArray) && ($retArray['statusCode'] === StatusCode::OK || $retArray['statusCode'] === StatusCode::LOGIN_ERROR)) {
                        return JsonResponse::create($retArray);
                    }
                    $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
                    $retArray['answer'] = $this->trans("errors.login");
                }
            }
        }

        $retArray['preHeaderMessages'] = "";
        return JsonResponse::create($retArray);
    }

    private function signInLdapUser($request, &$retArray) {
        /* @var $authenticator UserAuthenticator */
        $authenticator = $this->get('user_authenticator');
        $token = null;
        $username = $request->request->get('registrationUsername');

        try {
            $token = $authenticator->authenticate($username, $request->request->get('registrationPassword'));
            $retArray['statusCode'] = StatusCode::OK;
            $retArray['token'] = $token->getUser()->getUploadToken();
            $retArray['preHeaderMessages'] = '';
            return JsonResponse::create($retArray);
        } catch (UsernameNotFoundException $exception) {
            $user = null;
        } catch (AuthenticationException $exception) {
            $retArray['statusCode'] = StatusCode::LOGIN_ERROR;
            $retArray['answer'] = $this->trans('errors.login');
            $retArray['preHeaderMessages'] = '';
            return JsonResponse::create($retArray);
        }
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/IsOAuthUser/IsOAuthUser.json", name="catrobat_is_oauth_user", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function isOAuthUser(Request $request) {
        return $this->getOAuthService()->isOAuthUser($request);
    }

    /**
     * @Route("/api/EMailAvailable/EMailAvailable.json", name="catrobat_oauth_login_email_available", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function checkEMailAvailable(Request $request) {
        return $this->getOAuthService()->checkEMailAvailable($request);
    }

    /**
     * @Route("/api/UsernameAvailable/UsernameAvailable.json", name="catrobat_oauth_login_username_available", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function checkUserNameAvailable(Request $request) {
        return $this->getOAuthService()->checkUserNameAvailable($request);
    }

    /**
     * @Route("/api/FacebookServerTokenAvailable/FacebookServerTokenAvailable.json", name="catrobat_oauth_login_facebook_servertoken_available", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function checkFacebookServerTokenAvailable(Request $request) {
        return $this->getOAuthService()->checkFacebookServerTokenAvailable($request);
    }

    /**
     * @Route("/api/exchangeFacebookToken/exchangeFacebookToken.json", name="catrobat_oauth_login_facebook_token", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function exchangeFacebookTokenAction(Request $request) {
        return $this->getOAuthService()->exchangeFacebookTokenAction($request);
    }

    /**
     * @Route("/api/loginWithFacebook/loginWithFacebook.json", name="catrobat_oauth_login_facebook", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function loginWithFacebookAction(Request $request) {
        return $this->getOAuthService()->loginWithFacebookAction($request);
    }

    /**
     * @Route("/api/getFacebookUserInfo/getFacebookUserInfo.json", name="catrobat_facebook_userinfo", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function getFacebookUserProfileInfo(Request $request) {
        return $this->getOAuthService()->getFacebookUserProfileInfo($request);
    }

    /**
     * @Route("/api/checkFacebookServerTokenValidity/checkFacebookServerTokenValidity.json", name="catrobat_oauth_facebook_server_token_validity", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function isFacebookServerAccessTokenValid(Request $request) {
        return $this->getOAuthService()->isFacebookServerAccessTokenValid($request);
    }

    /**
     * @Route("/api/GoogleServerTokenAvailable/GoogleServerTokenAvailable.json", name="catrobat_oauth_login_google_servertoken_available", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function checkGoogleServerTokenAvailable(Request $request) {
        return $this->getOAuthService()->checkGoogleServerTokenAvailable($request);
    }

    /**
     * @Route("/api/exchangeGoogleCode/exchangeGoogleCode.json", name="catrobat_oauth_login_google_code", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function exchangeGoogleCodeAction(Request $request) {
        return $this->getOAuthService()->exchangeGoogleCodeAction($request);
    }

    /**
     * @Route("/api/loginWithGoogle/loginWithGoogle.json", name="catrobat_oauth_login_google", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function loginWithGoogleAction(Request $request) {
        return $this->getOAuthService()->loginWithGoogleAction($request);
    }

    /**
     * @Route("/api/getGoogleUserInfo/getGoogleUserInfo.json", name="catrobat_google_userinfo", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function getGoogleUserProfileInfo(Request $request) {
        return $this->getOAuthService()->getGoogleUserProfileInfo($request);
    }

    /**
     * @Route("/api/loginWithTokenAndRedirect/loginWithTokenAndRedirect", name="catrobat_oauth_login_redirect", options={"expose"=true})
     * @Method({"POST"})
     */
    public function loginWithTokenAndRedirectAction(Request $request) {
        return $this->getOAuthService()->loginWithTokenAndRedirectAction($request);
    }

    /**
     * @Route("/api/getFacebookAppId/getFacebookAppId.json", name="catrobat_oauth_login_get_facebook_appid", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function getFacebookAppId() {
        $retArray = array();
        $retArray['fb_appid'] = $this->container->getParameter('facebook_app_id');
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/getGoogleAppId/getGoogleAppId.json", name="catrobat_oauth_login_get_google_appid", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function getGoogleAppId() {
        $retArray = array();
        $retArray['gplus_appid'] = $this->container->getParameter('google_app_id');
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/generateCsrfToken/generateCsrfToken.json", name="catrobat_oauth_register_get_csrftoken", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function generateCsrfToken() {
        $retArray = array();
        $retArray['csrf_token'] = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');
        return JsonResponse::create($retArray);
    }

    /**
     * @Route("/api/deleteOAuthUserAccounts/deleteOAuthUserAccounts.json", name="catrobat_oauth_delete_testusers", options={"expose"=true}, defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function deleteOAuthTestUserAccounts() {
        return $this->getOAuthService()->deleteOAuthTestUserAccounts();
    }

    private function getOAuthService() {
        return $this->get("oauth_service");
    }

    private function trans($message, $parameters = array()) {
        return $this->get('translator')->trans($message, $parameters, 'catroweb');
    }
}
