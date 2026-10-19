<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\OAuth;

use App\DB\Entity\RefreshToken;
use App\DB\Entity\User\User;
use App\Security\OAuth\HwiOauthUserProvider;
use App\User\Achievements\AchievementManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @internal
 */
#[CoversClass(HwiOauthUserProvider::class)]
class HwiOauthUserProviderTest extends WebTestCase
{
  protected array $options = [
    'client_id' => 'clientid',
    'client_secret' => 'clientsecret',

    'infos_url' => 'http://user.info/?test=1',
    'authorization_url' => 'http://user.auth/?test=2',
    'access_token_url' => 'http://user.access/?test=3',

    'attr_name' => 'access_token',
  ];

  protected array $paths = [
    'identifier' => 'id',
    'nickname' => 'foo',
    'realname' => 'foo_disp',
    'firstname' => 'first_name',
    'lastname' => 'last_name',
    'email' => 'email',
  ];

  protected string $state = 'random';

  protected string $userResponse = <<<'json'
{
    "id":  "1",
    "foo": "bar",
    "email": "test@localhost.org",
    "first_name": "test",
    "last_name": "user"
}
json;

  protected array $properties = [
    'identifier' => 'id',
    'google' => 'id',
  ];

  protected array $tokenData = ['access_token' => 'token'];

  private HwiOauthUserProvider $object;

  private UserManager $user_manager;

  #[\Override]
  protected function setUp(): void
  {
    static::createClient();
    self::bootKernel();
    $container = static::getContainer();
    /** @var UserManager $user_manager */
    $user_manager = $container->get(UserManager::class);
    /** @var AchievementManager $achievement_manager */
    $achievement_manager = $container->get(AchievementManager::class);
    $this->user_manager = $user_manager;
    $this->object = new HwiOauthUserProvider($user_manager, $achievement_manager);
    $this->object->setProperties($this->properties);
  }

  /**
   * @throws Exception
   */
  public function testLoadUserByOauthResponse(): void
  {
    $httpClient = $this->createStub(HttpClientInterface::class);

    $storage = $this->createStub(RequestDataStorageInterface::class);

    $response_test = new PathUserResponse();

    $httpUtils = $this->createStub(HttpUtils::class);
    $resourceOwner = new GoogleResourceOwner(
      $httpClient,
      $httpUtils,
      $this->options,
      'google',
      $storage
    );
    $resourceOwner->addPaths(array_merge($this->paths, []));

    $response_test->setResourceOwner($resourceOwner);
    $response_test->setPaths($this->paths);
    $response_test->setData($this->userResponse);
    $response_test->setOAuthToken(new OAuthToken($this->tokenData));
    /**
     * @var User $response
     */
    $response = $this->object->loadUserByOAuthUserResponse($response_test);
    $this->assertEquals('testuser', $response->getUsername());
    $this->assertEquals('test@localhost.org', $response->getEmail());
    $this->assertEquals('token', $response->getGoogleAccessToken());
    $this->assertEquals('1', $response->getGoogleId());
    $this->assertTrue($response->isOauthUser());
    $this->assertNull($response->getFacebookAccessToken());
  }

  public function testSignInClaimsAnUnverifiedAccountWithTheSameEmail(): void
  {
    $squatter = $this->createUserWithRefreshToken('squatter7393', 'victim7393@localhost.org', false);
    $squatter->setFacebookId('squatters-own-facebook');
    $squatter->setDateOfBirth(new \DateTime('-10 years'));
    $squatter->setMinor(true);
    $squatter->setConsentStatus('pending');
    $squatter->setParentEmail('squatter-parent@localhost.org');
    $this->user_manager->updateUser($squatter);

    $user = $this->object->loadUserByOAuthUserResponse($this->googleResponse('google-7393-a', 'victim7393@localhost.org'));

    $this->assertInstanceOf(User::class, $user);
    $this->assertSame($squatter->getId(), $user->getId());
    $this->assertSame('google-7393-a', $user->getGoogleId());
    $this->assertTrue($user->isVerified());
    $this->assertFalse($this->user_manager->isPasswordValid($user, 'squatter-password'), 'the previous password must stop working');
    $this->assertSame(0, $this->countRefreshTokens('squatter7393'), 'refresh tokens of the previous holder must be revoked');
    $this->assertNull($user->getFacebookId(), 'provider identities linked by the previous holder must be removed');
    $this->assertNull($user->getParentEmail(), 'the previous holder\'s parent email must lose control of the account');
    $this->assertNull($user->getDateOfBirth(), 'the new owner completes registration again');
    $this->assertFalse($user->isMinor());
    $this->assertSame('not_required', $user->getConsentStatus());
  }

  public function testSignInRefusesAnUnverifiedAccountWhenTheProviderDidNotVerifyTheEmail(): void
  {
    $this->createUserWithRefreshToken('unverified7393', 'unverified7393@localhost.org', false);

    try {
      $this->object->loadUserByOAuthUserResponse($this->googleResponse('google-7393-c', 'unverified7393@localhost.org', false));
      $this->fail('linking must be refused');
    } catch (CustomUserMessageAccountStatusException) {
    }

    $this->entityManager()->clear();
    $user = $this->user_manager->findUserByUsername('unverified7393');
    $this->assertInstanceOf(User::class, $user);
    $this->assertNull($user->getGoogleId());
    $this->assertFalse($user->isVerified());
    $this->assertTrue($this->user_manager->isPasswordValid($user, 'squatter-password'));
  }

  public function testSignInKeepsTheCredentialsOfAVerifiedAccount(): void
  {
    $owner = $this->createUserWithRefreshToken('owner7393', 'owner7393@localhost.org', true);

    $user = $this->object->loadUserByOAuthUserResponse($this->googleResponse('google-7393-b', 'owner7393@localhost.org'));

    $this->assertInstanceOf(User::class, $user);
    $this->assertSame($owner->getId(), $user->getId());
    $this->assertTrue($this->user_manager->isPasswordValid($user, 'squatter-password'));
    $this->assertSame(1, $this->countRefreshTokens('owner7393'));
  }

  private function createUserWithRefreshToken(string $username, string $email, bool $verified): User
  {
    $user = $this->user_manager->create();
    $user->setUsername($username);
    $user->setEmail($email);
    $user->setPlainPassword('squatter-password');
    $user->setEnabled(true);
    $user->setVerified($verified);
    $this->user_manager->updateUser($user);

    /** @var RefreshTokenGeneratorInterface $generator */
    $generator = static::getContainer()->get(RefreshTokenGeneratorInterface::class);
    $refresh_token = $generator->createForUserWithTtl($user, 3600);
    $entity_manager = $this->entityManager();
    $entity_manager->persist($refresh_token);
    $entity_manager->flush();

    return $user;
  }

  private function countRefreshTokens(string $username): int
  {
    return $this->entityManager()->getRepository(RefreshToken::class)->count(['username' => $username]);
  }

  private function entityManager(): EntityManagerInterface
  {
    /** @var EntityManagerInterface $entity_manager */
    $entity_manager = static::getContainer()->get(EntityManagerInterface::class);

    return $entity_manager;
  }

  private function googleResponse(string $google_id, string $email, bool $email_verified = true): PathUserResponse
  {
    $resourceOwner = new GoogleResourceOwner(
      $this->createStub(HttpClientInterface::class),
      $this->createStub(HttpUtils::class),
      $this->options,
      'google',
      $this->createStub(RequestDataStorageInterface::class)
    );
    $resourceOwner->addPaths($this->paths);

    $response = new PathUserResponse();
    $response->setResourceOwner($resourceOwner);
    $response->setPaths($this->paths);
    $response->setData(json_encode(['id' => $google_id, 'email' => $email, 'verified_email' => $email_verified, 'first_name' => 'a', 'last_name' => 'b'], JSON_THROW_ON_ERROR));
    $response->setOAuthToken(new OAuthToken($this->tokenData));

    return $response;
  }
}
