<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\ProjectsApi;
use App\Api\Services\AuthenticationManager;
use App\Api\Services\Projects\ProjectsApiFacade;
use App\Api\Services\Projects\ProjectsApiLoader;
use App\Api\Services\Reactions\ReactionsApiFacade;
use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\Project\CodeView\CodeTreeBuilder;
use App\Project\Remix\RemixManager;
use App\Project\Scratch\ScratchManager;
use App\Storage\ScreenshotRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * @internal
 */
#[CoversClass(ProjectsApi::class)]
#[Group('unit')]
final class RemixGraphMinorMetadataTest extends TestCase
{
  #[DataProvider('viewers')]
  public function testRestrictedMetadataDependsOnViewerWithoutChangingTopology(?string $username, ?string $id, bool $minor, bool $hidden): void
  {
    $owner = new User();
    $owner->setUsername('Owner');
    $owner->setId('owner-id');
    $viewer = null;
    if (null !== $username) {
      $viewer = new User();
      $this->assertNotNull($id);
      $viewer->setId($id);
      $viewer->setUsername($username);
      $viewer->setMinor($minor);
    }
    $authentication = $this->createStub(AuthenticationManager::class);
    $authentication->method('getAuthenticatedUser')->willReturn($viewer);
    $loader = $this->createStub(ProjectsApiLoader::class);
    $loader->method('findProjectByID')->willReturn(new Project());
    $facade = $this->createStub(ProjectsApiFacade::class);
    $facade->method('getAuthenticationManager')->willReturn($authentication);
    $facade->method('getLoader')->willReturn($loader);
    $graph = [
      'projectId' => 'safe', 'projectCount' => 2, 'scratchCount' => 0, 'remixCount' => 1,
      'nodes' => [
        ['id' => 'catrobat_nfk', 'projectId' => 'nfk', 'source' => 'catrobat', 'available' => true, 'name' => 'Restricted', 'username' => $owner->getUserIdentifier(), '_ownerId' => $owner->getId(), 'notForKids' => true],
        ['id' => 'catrobat_safe', 'projectId' => 'safe', 'source' => 'catrobat', 'available' => true, 'name' => 'Safe', 'username' => 'Other', 'notForKids' => false],
      ],
      'edges' => [['id' => 'edge_0', 'from' => 'catrobat_nfk', 'to' => 'catrobat_safe']],
    ];
    $remix = $this->createStub(RemixManager::class);
    $remix->method('getRenderableRemixGraph')->willReturn($graph);
    $limiter = new RateLimiterFactory(['id' => 'remix-test', 'policy' => 'no_limit'], new InMemoryStorage());
    $api = new ProjectsApi($facade, $this->createStub(ReactionsApiFacade::class), $this->createStub(CodeTreeBuilder::class), $remix, $this->createStub(ScreenshotRepository::class), $limiter, $limiter, $limiter, new RequestStack(), new NullLogger(), $limiter, $limiter, $limiter, $this->createStub(ScratchManager::class));
    $code = 0;
    $headers = [];
    $result = $api->projectsIdRemixGraphGet('safe', $code, $headers);
    $this->assertSame(200, $code);
    $this->assertIsArray($result);
    $this->assertArrayNotHasKey('_ownerId', $result['nodes'][0]);
    $this->assertSame(!$hidden, $result['nodes'][0]['available']);
    $this->assertSame($hidden ? null : 'Restricted', $result['nodes'][0]['name']);
    $this->assertSame($hidden ? null : 'Owner', $result['nodes'][0]['username']);
    $this->assertSame('/images/default/not_available.png', $result['nodes'][0]['thumbnailUrl']);
    $this->assertSame('catrobat_nfk', $result['nodes'][0]['id']);
    $this->assertSame('nfk', $result['nodes'][0]['projectId']);
    $this->assertSame('Safe', $result['nodes'][1]['name']);
    $this->assertTrue($result['nodes'][1]['available']);
    $this->assertSame($graph['edges'], $result['edges']);
    $this->assertSame(2, $result['projectCount']);
    $this->assertSame(1, $result['remixCount']);
    $this->assertSame('private, no-store', $headers['Cache-Control']);

    // Exercise the real generated HTTP controller and serializer: null array values are omitted on the wire.
    $server = new \OpenAPI\Server\Api\ApiServer();
    $server->addApiHandler('projects', $api);
    $controller = new \OpenAPI\Server\Controller\ProjectsController();
    $controller->setApiServer($server);
    $controller->setSerializer(new \OpenAPI\Server\Service\JmsSerializer());
    $controller->setValidator(new \OpenAPI\Server\Service\SymfonyValidator(\Symfony\Component\Validator\Validation::createValidator()));
    $response = $controller->projectsIdRemixGraphGetAction(\Symfony\Component\HttpFoundation\Request::create('/api/projects/safe/remix-graph'), 'safe');
    $this->assertSame(200, $response->getStatusCode());
    $this->assertSame('no-store, private', $response->headers->get('Cache-Control'));
    $wire = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    $this->assertArrayNotHasKey('_ownerId', $wire['nodes'][0]);
    $this->assertSame(!$hidden, $wire['nodes'][0]['available']);
    if ($hidden) {
      $this->assertArrayNotHasKey('name', $wire['nodes'][0]);
      $this->assertArrayNotHasKey('username', $wire['nodes'][0]);
      $this->assertStringNotContainsString('Restricted', (string) $response->getContent());
      $this->assertStringNotContainsString('Owner', (string) $response->getContent());
    } else {
      $this->assertSame('Restricted', $wire['nodes'][0]['name']);
      $this->assertSame('Owner', $wire['nodes'][0]['username']);
    }
    $this->assertSame('Safe', $wire['nodes'][1]['name']);
    $this->assertSame('Other', $wire['nodes'][1]['username']);
    $this->assertSame('catrobat_nfk', $wire['nodes'][0]['id']);
    $this->assertSame('nfk', $wire['nodes'][0]['projectId']);
    $this->assertSame($graph['edges'], $wire['edges']);
    $this->assertSame(2, $wire['projectCount']);
    $this->assertSame(1, $wire['remixCount']);
  }

  /**
   * @return iterable<string, array{?string, ?string, bool, bool}>
   */
  public static function viewers(): iterable
  {
    yield 'minor viewer' => ['Minor', 'minor-id', true, true];
    yield 'minor owner' => ['Owner', 'owner-id', true, false];
    yield 'same username on a different account' => ['Owner', 'different-id', true, true];
    yield 'adult viewer' => ['Adult', 'adult-id', false, false];
    yield 'guest' => [null, null, false, false];
  }
}
