<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\DBUpdater;

use App\System\Commands\DBUpdater\UpdateUserRankingCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * Class UpdateUserRankingCommand.
 *
 * @internal
 *
 * @covers \App\System\Commands\DBUpdater\UpdateUserRankingCommand
 */
class UpdateUserRankingCommandTest extends KernelTestCase
{
  protected MockObject|UpdateUserRankingCommand $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UpdateUserRankingCommand::class)
      ->disableOriginalConstructor()
      ->getMockForAbstractClass()
    ;
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExists(): void
  {
    $this->assertTrue(class_exists(UpdateUserRankingCommand::class));
    $this->assertInstanceOf(UpdateUserRankingCommand::class, $this->object);
  }

  /**
   * @group integration
   *
   * @small
   */
  public function testTestClassExtends(): void
  {
    $this->assertInstanceOf(Command::class, $this->object);
  }
}
