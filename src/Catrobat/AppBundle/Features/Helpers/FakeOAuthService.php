<?php

namespace Catrobat\AppBundle\Features\Helpers;

use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Services\OAuthService;
use Catrobat\AppBundle\StatusCode;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FakeOAuthService
{
  private $oauth_service;
  private $use_real_oauth_service;
  private $container;

  public function __construct(OAuthService $oauth_service, Container $container)
  {
    $this->oauth_service = $oauth_service;
    $this->container = $container;
    $this->use_real_oauth_service = $container->getParameter('oauth_use_real_service');
  }

  public function isOAuthUser(Request $request)
  {
    return $this->oauth_service->isOAuthUser($request);
  }

  public function checkEMailAvailable(Request $request)
  {
    return $this->oauth_service->checkEMailAvailable($request);
  }

  public function checkUserNameAvailable(Request $request)
  {
    return $this->oauth_service->checkUserNameAvailable($request);
  }

  public function checkFacebookServerTokenAvailable(Request $request)
  {
    return $this->oauth_service->checkFacebookServerTokenAvailable($request);
  }

  public function exchangeFacebookTokenAction(Request $request)
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->exchangeFacebookTokenAction($request);
    }

    /**
     * @var $userManager UserManager
     * @var $user        User
     * @var $request     Request
     */
    $retArray = [];
    $userManager = $this->container->get("usermanager");
    $user = $userManager->findUserByEmail($request->get('email'));
    if ($user != null)
    {
      $retArray['statusCode'] = 200;
      $retArray['answer'] = 'Login successful!';
    }
    else
    {
      $user = $userManager->createUser();
      $user->setUsername('HeyWickieHey');
      $user->setEmail('pocket_zlxacqt_tester@tfbnw.net');
      $user->setPlainPassword('password');
      $retArray['statusCode'] = 201;
      $retArray['answer'] = 'Registration successful!';
    }
    $user->setFacebookUid('105678789764016');
    $user->setCountry('en_US');
    $user->setFacebookAccessToken('just invalid fake');
    $user->setEnabled(true);
    $userManager->updateUser($user);

    return JsonResponse::create($retArray);
  }

  public function loginWithFacebookAction(Request $request)
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->loginWithFacebookAction($request);
    }
    $retArray = [];
    $retArray['token'] = '123';
    $retArray['username'] = 'HeyWickieHey';

    return JsonResponse::create($retArray);
  }

  public function getFacebookUserProfileInfo(Request $request)
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->isOAuthUser($request);
    }
    throw new \Exception('Function not implemented in FakeOAuthService');
  }

  public function isFacebookServerAccessTokenValid(Request $request)
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->isOAuthUser($request);
    }
    throw new \Exception('Function not implemented in FakeOAuthService');
  }

  public function checkGoogleServerTokenAvailable(Request $request)
  {
    return $this->oauth_service->checkGoogleServerTokenAvailable($request);
  }

  public function exchangeGoogleCodeAction(Request $request)
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->exchangeGoogleCodeAction($request);
    }
    /**
     * @var $userManager UserManager
     * @var $user        User
     * @var $request     Request
     */
    $retArray = [];
    $userManager = $this->container->get("usermanager");
    $user = $userManager->findUserByEmail($request->get('email'));
    if ($user != null)
    {
      $retArray['statusCode'] = 200;
      $retArray['answer'] = 'Login successful!';
    }
    else
    {
      $user = $userManager->createUser();
      $user->setUsername('PocketGoogler');
      $user->setEmail('pocketcodetester@gmail.com');
      $user->setPlainPassword('password');
      $retArray['statusCode'] = 201;
      $retArray['answer'] = 'Registration successful!';
    }
    $user->setGplusUid('105155320106786463089');
    $user->setCountry('de');
    $user->setGplusAccessToken('just invalid fake');
    $user->setGplusIdToken('another fake');
    $user->setGplusRefreshToken('the worst fake');
    $user->setEnabled(true);
    $userManager->updateUser($user);

    return JsonResponse::create($retArray);
  }

  public function loginWithGoogleAction(Request $request)
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->loginWithGoogleAction($request);
    }
    $retArray = [];
    $retArray['token'] = '123';
    $retArray['username'] = 'PocketGoogler';

    return JsonResponse::create($retArray);
  }

  public function getGoogleUserProfileInfo(Request $request)
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->isOAuthUser($request);
    }
    throw new \Exception('Function not implemented in FakeOAuthService');
  }

  public function loginWithTokenAndRedirectAction(Request $request)
  {
    return $this->oauth_service->loginWithTokenAndRedirectAction($request);
  }

  public function deleteOAuthTestUserAccounts()
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->deleteOAuthTestUserAccounts();
    }
    throw new \Exception('Function not implemented in FakeOAuthService');
  }

  public function useRealService($use_real)
  {
    $this->use_real_oauth_service = $use_real;
  }

  public function getUseRealOauthService()
  {
    return $this->use_real_oauth_service;
  }
}
