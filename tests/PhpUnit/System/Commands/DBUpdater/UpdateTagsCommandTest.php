<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\DBUpdater;

use App\System\Commands\DBUpdater\UpdateTagsCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * Class UpdateTagsCommand.
 *
 * @internal
 *
 * @covers \App\System\Commands\DBUpdater\UpdateTagsCommand
 */
class UpdateTagsCommandTest extends KernelTestCase
{
  protected MockObject|UpdateTagsCommand $object;

  #[\Override]
  protected function setUp(): void
  {
    $this->object = $this->getMockBuilder(UpdateTagsCommand::class)
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
    $this->assertTrue(class_exists(UpdateTagsCommand::class));
    $this->assertInstanceOf(UpdateTagsCommand::class, $this->object);
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
