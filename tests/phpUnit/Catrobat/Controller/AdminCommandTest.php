<?php

namespace Tests\phpUnit\Catrobat\Controller;

use App\Catrobat\Controller\Admin\AdminCommand;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Catrobat\Controller\Admin\AdminCommand
 *
 * @internal
 */
class AdminCommandTest extends TestCase
{
  private AdminCommand $admin_command;

  protected function setUp(): void
  {
    $this->admin_command = new AdminCommand();
  }

  public function testAdminCommand(): void
  {
    $this->assertInstanceOf(AdminCommand::class, $this->admin_command);
    $this->admin_command->setCommandLink('test');
    $this->assertSame('test', $this->admin_command->command_link);
    $this->admin_command->setCommandName('test');
    $this->assertSame('test', $this->admin_command->command_name);
    $this->admin_command->setProgressLink('test');
    $this->assertSame('test', $this->admin_command->getProgressLink());
  }
}
