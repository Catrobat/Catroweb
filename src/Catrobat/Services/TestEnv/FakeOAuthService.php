<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\OAuthService;
use App\Catrobat\Services\TokenGenerator;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FakeOAuthService extends OAuthService
{
  private OAuthService $oauth_service;

  private bool $use_real_oauth_service;

  private UserManager $user_manager;

  public function __construct(OAuthService $oauth_service, ParameterBagInterface $parameter_bag,
                              UserManager $user_manager, ValidatorInterface $validator, ProgramManager $program_manager,
                              EntityManagerInterface $em, TranslatorInterface $translator,
                              TokenStorageInterface $token_storage, EventDispatcherInterface $dispatcher,
                              RouterInterface $router, TokenGenerator $token_generator)
  {
    parent::__construct($user_manager, $parameter_bag, $validator, $program_manager, $em, $translator,
                              $token_storage, $dispatcher, $router, $token_generator);

    $this->oauth_service = $oauth_service;
    try
    {
      $this->use_real_oauth_service = boolval($parameter_bag->get('oauth_use_real_service'));
    }
    catch (Exception $exception)
    {
      $this->use_real_oauth_service = false;
    }

    $this->user_manager = $user_manager;
  }

  /**
   * @throws Exception
   */
  public function isOAuthUser(Request $request): JsonResponse
  {
    return $this->oauth_service->isOAuthUser($request);
  }

  /**
   * @throws Exception
   */
  public function checkEMailAvailable(Request $request): JsonResponse
  {
    return $this->oauth_service->checkEMailAvailable($request);
  }

  /**
   * @throws Exception
   */
  public function checkUserNameAvailable(Request $request): JsonResponse
  {
    return $this->oauth_service->checkUserNameAvailable($request);
  }

  /**
   * @throws Exception
   */
  public function checkGoogleServerTokenAvailable(Request $request): JsonResponse
  {
    return $this->oauth_service->checkGoogleServerTokenAvailable($request);
  }

  /**
   * @throws Exception
   */
  public function exchangeGoogleCodeAction(Request $request): JsonResponse
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->exchangeGoogleCodeAction($request);
    }
    $retArray = [];

    /** @var User|null $user */
    $user = $this->user_manager->findUserByEmail($request->get('email'));
    if (null !== $user)
    {
      $retArray['statusCode'] = 200;
      $retArray['answer'] = 'Login successful!';
    }
    else
    {
      /** @var User $user */
      $user = $this->user_manager->createUser();
      $user->setUsername('PocketGoogler');
      $user->setEmail('pocketcodetester@gmail.com');
      $user->setPlainPassword('password');
      $retArray['statusCode'] = 201;
      $retArray['answer'] = 'Registration successful!';
    }
    /* @var User|null $user */
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
   * @throws Exception
   */
  public function loginWithGoogleAction(Request $request): JsonResponse
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
   * @throws Exception
   */
  public function getGoogleUserProfileInfo(Request $request): JsonResponse
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->isOAuthUser($request);
    }
    throw new Exception('Function not implemented in FakeOAuthService');
  }

  /**
   * @throws Exception
   */
  public function loginWithTokenAndRedirectAction(Request $request): JsonResponse
  {
    return $this->oauth_service->loginWithTokenAndRedirectAction($request);
  }

  /**
   * @throws Exception
   */
  public function deleteOAuthTestUserAccounts(): JsonResponse
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->deleteOAuthTestUserAccounts();
    }
    throw new Exception('Function not implemented in FakeOAuthService');
  }

  public function useRealService(bool $use_real): void
  {
    $this->use_real_oauth_service = $use_real;
  }

  public function getUseRealOauthService(): bool
  {
    return $this->use_real_oauth_service;
  }
}
