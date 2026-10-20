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
  public function testRestrictedMetadataDependsOnViewerWithoutChangingTopology(?string $username, bool $minor, bool $hidden): void
  {
    $viewer = null;
    if (null !== $username) {
      $viewer = new User();
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
        ['id' => 'catrobat_nfk', 'projectId' => 'nfk', 'source' => 'catrobat', 'available' => true, 'name' => 'Restricted', 'username' => 'Owner', 'notForKids' => true],
        ['id' => 'catrobat_safe', 'projectId' => 'safe', 'source' => 'catrobat', 'available' => true, 'name' => 'Safe', 'username' => 'Other', 'notForKids' => false],
      ],
      'edges' => [['id' => 'edge_0', 'from' => 'catrobat_nfk', 'to' => 'catrobat_safe']],
    ];
    $remix = $this->createStub(RemixManager::class);
    $remix->method('getRenderableRemixGraph')->willReturn($graph);
    $limiter = new RateLimiterFactory(['id' => 'remix-test', 'policy' => 'no_limit'], new InMemoryStorage());
    $api = new ProjectsApi($facade, $this->createStub(ReactionsApiFacade::class), $this->createStub(CodeTreeBuilder::class), $remix, $this->createStub(ScreenshotRepository::class), $limiter, $limiter, $limiter, new RequestStack(), new NullLogger());
    $code = 0;
    $headers = [];
    $result = $api->projectsIdRemixGraphGet('safe', $code, $headers);
    $this->assertSame(200, $code);
    $this->assertIsArray($result);
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
  }

  /**
   * @return iterable<string, array{?string, bool, bool}>
   */
  public static function viewers(): iterable
  {
    yield 'minor viewer' => ['Minor', true, true];
    yield 'minor owner' => ['Owner', true, false];
    yield 'adult viewer' => ['Adult', false, false];
    yield 'guest' => [null, false, false];
  }
}
