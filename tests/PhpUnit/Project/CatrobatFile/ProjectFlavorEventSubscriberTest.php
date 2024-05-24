<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Program;
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
  private ProjectFlavorEventSubscriber $program_flavor_listener;

  private RequestStack $stack;

  protected function setUp(): void
  {
    $this->stack = new RequestStack();
    $this->program_flavor_listener = new ProjectFlavorEventSubscriber($this->stack);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProjectFlavorEventSubscriber::class, $this->program_flavor_listener);
  }

  public function testSetsTheFlavorOfAProgramBasedOnItsRequestFlavor(): void
  {
    $program = new Program();
    $request = new Request();

    $request->attributes->set('flavor', Flavor::POCKETCODE);
    $this->stack->push($request);
    $this->program_flavor_listener->checkFlavor($program);
    Assert::assertEquals(Flavor::POCKETCODE, $program->getFlavor());

    $request->attributes->set('flavor', Flavor::PHIROCODE);
    $this->stack->push($request);
    $this->program_flavor_listener->checkFlavor($program);
    Assert::assertEquals(Flavor::PHIROCODE, $program->getFlavor());
  }
}
