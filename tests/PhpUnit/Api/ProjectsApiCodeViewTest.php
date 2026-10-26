<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use App\Api\ProjectsApi;
use App\Api\Services\Projects\ProjectsApiFacade;
use App\Api\Services\Projects\ProjectsApiLoader;
use App\Api\Services\Reactions\ReactionsApiFacade;
use App\DB\Entity\Project\Project;
use App\Project\CodeView\CodeExportUnavailableException;
use App\Project\CodeView\CodeTreeBuilder;
use App\Project\CodeView\CodeTreeBuildException;
use App\Project\Remix\RemixManager;
use App\Project\Scratch\ScratchManager;
use App\Storage\ScreenshotRepository;
use OpenAPI\Server\Model\CodeViewResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

/**
 * GET /projects/{id}/code: the code view, 422 (never a 500) when a project's code cannot be shown, 503 while it is
 * being made.
 *
 * @internal
 */
#[CoversClass(ProjectsApi::class)]
#[Group('unit')]
final class ProjectsApiCodeViewTest extends TestCase
{
  public function testTheCodeView(): void
  {
    $builder = $this->createStub(CodeTreeBuilder::class);
    $builder->method('buildCodeTree')->willReturn(['scenes' => [['name' => 'Scene 1', 'objects' => []]]]);
    $code = 0;
    $headers = [];

    $response = $this->api(new Project(), $builder)->projectsIdCodeGet('p1', $code, $headers);

    $this->assertSame(200, $code);
    $this->assertInstanceOf(CodeViewResponse::class, $response);
  }

  public function testACodeViewThatCannotBeShown(): void
  {
    $builder = $this->createStub(CodeTreeBuilder::class);
    $builder->method('buildCodeTree')->willThrowException(new CodeTreeBuildException('code.xml is not valid XML'));
    $code = 0;
    $headers = [];

    $this->assertNull($this->api(new Project(), $builder)->projectsIdCodeGet('p1', $code, $headers));
    $this->assertSame(422, $code);
  }

  public function testACodeViewThatIsBeingMade(): void
  {
    $builder = $this->createStub(CodeTreeBuilder::class);
    $builder->method('buildCodeTree')->willThrowException(new CodeExportUnavailableException('All 2 code export slots are busy'));
    $code = 0;
    $headers = [];

    $this->assertNull($this->api(new Project(), $builder)->projectsIdCodeGet('p1', $code, $headers));
    $this->assertSame(503, $code);
    $this->assertSame('5', $headers['Retry-After']);
  }

  public function testAProjectThatDoesNotExist(): void
  {
    $code = 0;
    $headers = [];

    $this->assertNull($this->api(null, $this->createStub(CodeTreeBuilder::class))->projectsIdCodeGet('p1', $code, $headers));
    $this->assertSame(404, $code);
  }

  private function api(?Project $project, CodeTreeBuilder $builder): ProjectsApi
  {
    $loader = $this->createStub(ProjectsApiLoader::class);
    $loader->method('findProjectByID')->willReturn($project);
    $facade = $this->createStub(ProjectsApiFacade::class);
    $facade->method('getLoader')->willReturn($loader);
    $limiter = new RateLimiterFactory(['id' => 'code-view-test', 'policy' => 'no_limit'], new InMemoryStorage());

    return new ProjectsApi($facade, $this->createStub(ReactionsApiFacade::class), $builder, $this->createStub(RemixManager::class), $this->createStub(ScreenshotRepository::class), $limiter, $limiter, $limiter, new RequestStack(), new NullLogger(), $limiter, $limiter, $limiter, $this->createStub(ScratchManager::class));
  }
}
