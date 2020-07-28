<?php

namespace Tests\phpUnit\OAuth;

use App\Catrobat\Security\FOSUBUserProvider;
use App\Entity\User;
use App\Entity\UserManager;
use Http\Client\Common\HttpMethodsClient;
use Http\Client\HttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @internal
 * @coversNothing
 */
class OAuthTest extends WebTestCase
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
  private FOSUBUserProvider $fosub_user_provider;

  protected function setUp(): void
  {
    static::createClient();
    $user_manager = static::$container->get(UserManager::class);
    $this->fosub_user_provider = new FOSUBUserProvider($user_manager, $this->properties);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(FOSUBUserProvider::class, $this->fosub_user_provider);
  }

  public function testLoadUserByOauthResponse(): void
  {
    $httpClient = $this->createMock(HttpClient::class);

    $storage = $this->createMock(RequestDataStorageInterface::class);

    $response_test = new PathUserResponse();

    /** @var HttpUtils $httpUtils */
    $httpUtils = $this->createMock(HttpUtils::class);
    $resourceOwner = new GoogleResourceOwner(new HttpMethodsClient($httpClient, new GuzzleMessageFactory()),
      $httpUtils, $this->options, 'google', $storage);
    $resourceOwner->addPaths(array_merge($this->paths, []));
    $response_test->setResourceOwner($resourceOwner);
    $response_test->setPaths($this->paths);
    $response_test->setData($this->userResponse);
    $response_test->setOAuthToken(new OAuthToken($this->tokenData));
    /**
     * @var User $response
     */
    $response = $this->fosub_user_provider->loadUserByOAuthUserResponse($response_test);
    $this->assertInstanceOf(User::class, $response);
    $this->assertEquals('testuser', $response->getUsername());
    $this->assertEquals('test@localhost.org', $response->getEmail());
    $this->assertEquals('token', $response->getGoogleAccessToken());
    $this->assertEquals('1', $response->getGoogleId());
    $this->assertEquals(true, $response->isOauthUser());
    $this->assertNull($response->getFacebookAccessToken());
  }
}
