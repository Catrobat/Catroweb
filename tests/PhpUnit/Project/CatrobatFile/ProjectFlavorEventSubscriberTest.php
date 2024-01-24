<?php

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\DB\Entity\Project\Project;
use App\Project\CatrobatFile\ProjectFlavorEventSubscriber;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 *
 * @covers  \App\Project\CatrobatFile\ProjectFlavorEventSubscriber
 */
class ProjectFlavorEventSubscriberTest extends TestCase
{
  private ProjectFlavorEventSubscriber $project_flavor_listener;

  private RequestStack $stack;

  protected function setUp(): void
  {
    $this->stack = new RequestStack();
    $this->project_flavor_listener = new ProjectFlavorEventSubscriber($this->stack);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProjectFlavorEventSubscriber::class, $this->project_flavor_listener);
  }

  public function testSetsTheFlavorOfAProjectBasedOnItsRequestFlavor(): void
  {
    $project = new Project();
    $request = new Request();

    $request->attributes->set('flavor', 'pocketcode');
    $this->stack->push($request);
    $this->project_flavor_listener->checkFlavor($project);
    Assert::assertEquals('pocketcode', $project->getFlavor());

    $request->attributes->set('flavor', 'pocketphiro');
    $this->stack->push($request);
    $this->project_flavor_listener->checkFlavor($project);
    Assert::assertEquals('pocketphiro', $project->getFlavor());
  }
}
