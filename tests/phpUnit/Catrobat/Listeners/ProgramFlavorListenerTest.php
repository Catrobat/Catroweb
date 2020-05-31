<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Listeners\ProgramFlavorListener;
use App\Entity\Program;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 * @coversNothing
 */
class ProgramFlavorListenerTest extends TestCase
{
  private ProgramFlavorListener $program_flavor_listener;

  /**
   * @var MockObject|RequestStack
   */
  private $stack;

  protected function setUp(): void
  {
    $this->stack = $this->createMock(RequestStack::class);
    $this->program_flavor_listener = new ProgramFlavorListener($this->stack);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProgramFlavorListener::class, $this->program_flavor_listener);
  }

  public function testSetsTheFlavorOfAProgramBasedOnItsRequestFlavor(): void
  {
    $program = new Program();
    $request = new Request();

    $request->attributes->set('flavor', 'pocketcode');
    $this->stack->expects($this->atLeastOnce())->method('getCurrentRequest')->willReturn($request);
    $this->program_flavor_listener->checkFlavor($program);
    Assert::assertEquals($program->getFlavor(), 'pocketcode');

    $request->attributes->set('flavor', 'pocketphiro');
    $this->stack->expects($this->atLeastOnce())->method('getCurrentRequest')->willReturn($request);
    $this->program_flavor_listener->checkFlavor($program);
    Assert::assertEquals($program->getFlavor(), 'pocketphiro');
  }
}
