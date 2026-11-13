<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\Maintenance;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\Project\AddProjectRequest;
use App\Project\ProjectManager;
use App\System\Commands\Maintenance\RestoreProjectFilesCommand;
use App\User\UserManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(RestoreProjectFilesCommand::class)]
class RestoreProjectFilesCommandTest extends TestCase
{
  private string $dir;

  #[\Override]
  protected function setUp(): void
  {
    $this->dir = sys_get_temp_dir().'/restore-files-'.uniqid();
    mkdir($this->dir);
    $this->archive('a.catrobat', 'Known');
    $this->archive('b.catrobat', 'Unknown');
  }

  #[\Override]
  protected function tearDown(): void
  {
    array_map(unlink(...), glob($this->dir.'/*') ?: []);
    rmdir($this->dir);
  }

  public function testUpdatesTheExistingProjectAndSkipsArchivesWithoutOne(): void
  {
    $project = $this->createStub(Project::class);
    $project->method('getId')->willReturn('p1');
    $project->method('getFlavor')->willReturn('luna');

    $project_manager = $this->createMock(ProjectManager::class);
    $project_manager->method('findOneByNameAndUser')->willReturnCallback(static fn (string $name): ?Project => 'Known' === $name ? $project : null);
    $project_manager->expects($this->once())->method('addProject')
      ->with($this->callback(static fn (AddProjectRequest $request): bool => 'p1' === $request->getProjectId() && 'luna' === $request->getFlavor()))
    ;

    $tester = $this->runCommand($project_manager, []);

    self::assertSame(Command::SUCCESS, $tester->getStatusCode());
    self::assertStringContainsString('1 restored, 1 skipped, 0 failed.', $tester->getDisplay());
  }

  public function testDryRunUploadsNothing(): void
  {
    $project_manager = $this->createMock(ProjectManager::class);
    $project_manager->method('findOneByNameAndUser')->willReturn($this->createStub(Project::class));
    $project_manager->expects($this->never())->method('addProject');

    $tester = $this->runCommand($project_manager, ['--dry-run' => true]);

    self::assertStringContainsString('2 restored, 0 skipped, 0 failed.', $tester->getDisplay());
  }

  /**
   * @param array<string, mixed> $options
   */
  private function runCommand(ProjectManager $project_manager, array $options): CommandTester
  {
    $user_manager = $this->createStub(UserManager::class);
    $user_manager->method('findUserByUsername')->willReturn($this->createStub(User::class));

    $tester = new CommandTester(new RestoreProjectFilesCommand($user_manager, $project_manager));
    $tester->execute(['directory' => $this->dir, 'user' => 'catrobat'] + $options);

    return $tester;
  }

  private function archive(string $file, string $name): void
  {
    $zip = new \ZipArchive();
    $zip->open($this->dir.'/'.$file, \ZipArchive::CREATE);
    $zip->addFromString('code.xml', '<?xml version="1.0"?><program><header><programName>'.$name.'</programName></header></program>');
    $zip->close();
  }
}
