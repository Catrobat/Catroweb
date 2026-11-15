<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\Maintenance;

use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Moderation\ContentReportRepository;
use App\DB\Enum\ReportState;
use App\Moderation\ReportProcessor;
use App\System\Commands\Maintenance\ResolveReportsCommand;
use App\User\UserManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(ResolveReportsCommand::class)]
class ResolveReportsCommandTest extends TestCase
{
  public function testResolvesThroughReportProcessorStartingAtFirstPendingReport(): void
  {
    $resolved = new ContentReport();
    $resolved->setState(ReportState::Rejected->value);
    $pending = new ContentReport();

    $admin = new User();
    $admin->addRole('ROLE_ADMIN');

    $repository = $this->createStub(ContentReportRepository::class);
    $repository->method('findReportsForContent')->willReturn([$resolved, $pending]);
    $user_manager = $this->createStub(UserManager::class);
    $user_manager->method('findUserByUsername')->willReturn($admin);

    $processor = $this->createMock(ReportProcessor::class);
    $processor->expects($this->once())->method('resolveReport')
      ->with($pending, $admin, ReportProcessor::ACTION_HIDE_WARN, 'Be kind')
    ;

    $tester = new CommandTester(new ResolveReportsCommand($repository, $processor, $user_manager));
    $tester->execute([
      'content_type' => 'project',
      'content_id' => 'project-1',
      'action' => 'hide_warn',
      '--admin' => 'someadmin',
      '--message' => 'Be kind',
    ]);

    self::assertSame(Command::SUCCESS, $tester->getStatusCode());
    self::assertStringContainsString('1 report(s) resolved', $tester->getDisplay());
  }

  public function testRejectsNonAdminAndUnknownAction(): void
  {
    $processor = $this->createMock(ReportProcessor::class);
    $processor->expects($this->never())->method('resolveReport');
    $user_manager = $this->createStub(UserManager::class);
    $user_manager->method('findUserByUsername')->willReturn(new User());

    $tester = new CommandTester(new ResolveReportsCommand($this->createStub(ContentReportRepository::class), $processor, $user_manager));

    self::assertSame(Command::INVALID, $tester->execute(['content_type' => 'project', 'content_id' => '1', 'action' => 'warn', '--admin' => 'x']));
    self::assertSame(Command::INVALID, $tester->execute(['content_type' => 'project', 'content_id' => '1', 'action' => 'ban', '--admin' => 'x']));
  }
}
