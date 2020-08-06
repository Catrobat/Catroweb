<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Listeners\ProgramFlavorListener;
use App\Entity\Program;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 * @covers  \App\Catrobat\Listeners\ProgramFlavorListener
 */
class ProgramFlavorListenerTest extends TestCase
{
  private ProgramFlavorListener $program_flavor_listener;

  private RequestStack $stack;

  protected function setUp(): void
  {
    $this->stack = new RequestStack();
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
    $this->stack->push($request);
    $this->program_flavor_listener->checkFlavor($program);
    Assert::assertEquals('pocketcode', $program->getFlavor());

    $request->attributes->set('flavor', 'pocketphiro');
    $this->stack->push($request);
    $this->program_flavor_listener->checkFlavor($program);
    Assert::assertEquals('pocketphiro', $program->getFlavor());
  }
}
