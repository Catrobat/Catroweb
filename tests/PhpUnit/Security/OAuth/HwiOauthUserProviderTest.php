<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Security\OAuth;

use App\DB\Entity\User\User;
use App\Security\OAuth\HwiOauthUserProvider;
use App\User\UserManager;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
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

  #[\Override]
  protected function setUp(): void
  {
    static::createClient();
    self::bootKernel();
    $container = static::getContainer();
    /** @var UserManager $user_manager */
    $user_manager = $container->get(UserManager::class);
    $this->object = new HwiOauthUserProvider($user_manager);
    $this->object->setProperties($this->properties);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(HwiOauthUserProvider::class, $this->object);
  }

  /**
   * @throws Exception
   */
  public function testLoadUserByOauthResponse(): void
  {
    $httpClient = $this->createMock(HttpClientInterface::class);

    $storage = $this->createMock(RequestDataStorageInterface::class);

    $response_test = new PathUserResponse();

    $httpUtils = $this->createMock(HttpUtils::class);
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
    $this->assertInstanceOf(User::class, $response);
    $this->assertEquals('testuser', $response->getUsername());
    $this->assertEquals('test@localhost.org', $response->getEmail());
    $this->assertEquals('token', $response->getGoogleAccessToken());
    $this->assertEquals('1', $response->getGoogleId());
    $this->assertTrue($response->isOauthUser());
    $this->assertNull($response->getFacebookAccessToken());
  }
}
