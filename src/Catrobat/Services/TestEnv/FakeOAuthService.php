<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\TokenGenerator;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use App\Catrobat\Services\OAuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * Class FakeOAuthService
 * @package App\Catrobat\Features\Helpers
 */
class FakeOAuthService extends OAuthService
{
  /**
   * @var OAuthService
   */
  private $oauth_service;

  /**
   * @var mixed
   */
  private $use_real_oauth_service;

  /**
   * @var UserManager
   */
  private $user_manager;

  /**
   * FakeOAuthService constructor.
   *
   * @param OAuthService $oauth_service
   * @param ParameterBagInterface $parameter_bag
   * @param UserManager $user_manager
   * @param ValidatorInterface $validator
   * @param ProgramManager $program_manager
   * @param EntityManagerInterface $em
   * @param TranslatorInterface $translator
   * @param TokenStorageInterface $token_storage
   * @param EventDispatcherInterface $dispatcher
   * @param RouterInterface $router
   * @param TokenGenerator $token_generator
   */
  public function __construct(OAuthService $oauth_service, ParameterBagInterface $parameter_bag,
                              UserManager $user_manager, ValidatorInterface $validator, ProgramManager $program_manager,
                              EntityManagerInterface $em, TranslatorInterface $translator,
                              TokenStorageInterface $token_storage, EventDispatcherInterface $dispatcher,
                              RouterInterface $router, TokenGenerator $token_generator)
  {
    parent::__construct($user_manager, $parameter_bag, $validator, $program_manager, $em, $translator,
                              $token_storage, $dispatcher, $router, $token_generator);

    $this->oauth_service = $oauth_service;
    try {
      $this->use_real_oauth_service = $parameter_bag->get('oauth_use_real_service');
    }
    catch (\Exception $e) {
      $this->use_real_oauth_service = false;
    }

    $this->user_manager = $user_manager;
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Exception
   */
  public function isOAuthUser(Request $request)
  {
    return $this->oauth_service->isOAuthUser($request);
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Exception
   */
  public function checkEMailAvailable(Request $request)
  {
    return $this->oauth_service->checkEMailAvailable($request);
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Exception
   */
  public function checkUserNameAvailable(Request $request)
  {
    return $this->oauth_service->checkUserNameAvailable($request);
  }


  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Exception
   */
  public function checkGoogleServerTokenAvailable(Request $request)
  {
    return $this->oauth_service->checkGoogleServerTokenAvailable($request);
  }

  /**
   * @param Request $request
   *
   * @return OAuthService|JsonResponse
   * @throws \Exception
   */
  public function exchangeGoogleCodeAction(Request $request)
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->exchangeGoogleCodeAction($request);
    }
    /**
     * @var $user        User
     * @var $request     Request
     */
    $retArray = [];
    $user = $this->user_manager->findUserByEmail($request->get('email'));
    if ($user != null)
    {
      $retArray['statusCode'] = 200;
      $retArray['answer'] = 'Login successful!';
    }
    else
    {
      $user = $this->user_manager->createUser();
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
    $this->user_manager->updateUser($user);

    return JsonResponse::create($retArray);
  }

  /**
   * @param Request $request
   *
   * @return OAuthService|JsonResponse
   * @throws \Exception
   */
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

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Exception
   */
  public function getGoogleUserProfileInfo(Request $request)
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->isOAuthUser($request);
    }
    throw new \Exception('Function not implemented in FakeOAuthService');
  }

  /**
   * @param Request $request
   *
   * @return JsonResponse
   * @throws \Exception
   */
  public function loginWithTokenAndRedirectAction(Request $request)
  {
    return $this->oauth_service->loginWithTokenAndRedirectAction($request);
  }

  /**
   * @return JsonResponse
   * @throws \Exception
   */
  public function deleteOAuthTestUserAccounts()
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->deleteOAuthTestUserAccounts();
    }
    throw new \Exception('Function not implemented in FakeOAuthService');
  }

  /**
   * @param $use_real
   */
  public function useRealService($use_real)
  {
    $this->use_real_oauth_service = $use_real;
  }

  /**
   * @return mixed
   */
  public function getUseRealOauthService()
  {
    return $this->use_real_oauth_service;
  }
}
