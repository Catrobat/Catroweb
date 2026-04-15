<?php

declare(strict_types=1);

namespace App\Application\Controller\Dev;

use App\DB\Entity\User\User;
use App\Security\Authentication\CookieService;
use App\Security\Authentication\JwtRefresh\RefreshTokenService;
use App\Security\Authentication\MainFirewallSessionLogin;
use App\Security\PasswordGenerator;
use App\User\Achievements\AchievementManager;
use App\User\UserManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Dev-only controller that simulates Google OAuth login without hitting Google.
 * Only accessible when kernel.environment is dev or test.
 */
class FakeOAuthController extends AbstractController
{
  private const string FIREWALL_NAME = 'main';

  public function __construct(
    private readonly UserManager $user_manager,
    private readonly AchievementManager $achievement_manager,
    private readonly CookieService $cookie_service,
    private readonly JWTTokenManagerInterface $jwt_manager,
    private readonly RefreshTokenService $refresh_token_service,
    private readonly MainFirewallSessionLogin $main_firewall_session_login,
    #[Autowire(param: 'kernel.environment')]
    private readonly string $app_env,
  ) {
  }

  #[Route(path: '/dev/fake-oauth', name: 'dev_fake_oauth', methods: ['GET'])]
  public function showForm(): Response
  {
    if (!in_array($this->app_env, ['dev', 'test'], true)) {
      throw $this->createNotFoundException();
    }

    return $this->render('Dev/FakeOAuth.html.twig');
  }

  #[Route(path: '/dev/fake-oauth', name: 'dev_fake_oauth_submit', methods: ['POST'])]
  public function submitForm(Request $request): Response
  {
    if (!in_array($this->app_env, ['dev', 'test'], true)) {
      throw $this->createNotFoundException();
    }

    $email = trim($request->request->getString('email', 'fake-google-user@example.com'));
    $firstName = trim($request->request->getString('first_name', 'Dev'));
    $lastName = trim($request->request->getString('last_name', 'User'));

    if ('' === $email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $email = 'fake-google-user@example.com';
    }

    $fakeGoogleId = 'fake-google-'.md5($email);

    $user = $this->user_manager->findOneBy(['google_id' => $fakeGoogleId]);
    if (!$user instanceof User) {
      $user = $this->user_manager->findUserByEmail($email);
      if (!$user instanceof User) {
        $user = $this->user_manager->create();
        $user->setUsername($this->generateUsername($firstName, $lastName));
        $user->setEmail($email);
        $user->setEnabled(true);
        $user->setPassword(PasswordGenerator::generateRandomPassword());
        $user->setOauthUser(true);
        $user->setVerified(true);
        $isNew = true;
      } else {
        $isNew = false;
      }

      $user->setGoogleId($fakeGoogleId);
      $user->setGoogleAccessToken('fake-dev-token');
      $this->user_manager->updateUser($user);

      if ($isNew) {
        $this->achievement_manager->unlockAchievementAccountVerification($user);
      }
    }

    $token = new UsernamePasswordToken($user, self::FIREWALL_NAME, $user->getRoles());
    $this->main_firewall_session_login->login($request, $token);

    $refreshToken = $this->refresh_token_service->createRefreshTokenForUsername($user->getUserIdentifier());
    $redirectUrl = null === $user->getDateOfBirth()
      ? $this->generateUrl('complete_registration')
      : '/';

    $response = new RedirectResponse($redirectUrl);
    $rawRefreshToken = $refreshToken->getRefreshToken();
    if (null !== $rawRefreshToken) {
      $response->headers->setCookie($this->cookie_service->createRefreshTokenCookie($rawRefreshToken));
    }

    $response->headers->setCookie($this->cookie_service->createBearerTokenCookie($this->jwt_manager->create($user)));

    return $response;
  }

  private function generateUsername(string $firstName, string $lastName): string
  {
    $base = $firstName.$lastName;
    if ('' === $base) {
      $base = 'user';
    }

    $username = $base;
    $counter = 0;
    while (null !== $this->user_manager->findUserByUsername($username)) {
      ++$counter;
      $username = $base.$counter;
    }

    return $username;
  }
}
