<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\ImportProjects;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\Project\AddProjectRequest;
use App\Security\Malware\MalwareScanUnavailableException;
use App\System\Commands\Helpers\RemixManipulationProjectManager;
use App\System\Commands\ImportProjects\ProjectImportCommand;
use App\User\UserManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(ProjectImportCommand::class)]
final class ProjectImportCommandTest extends TestCase
{
  private string $directory;

  #[\Override]
  protected function setUp(): void
  {
    $this->directory = sys_get_temp_dir().'/project-import-test-'.bin2hex(random_bytes(4));
    mkdir($this->directory);
    touch($this->directory.'/a-unscanned.catrobat');
    touch($this->directory.'/b-fine.catrobat');
  }

  #[\Override]
  protected function tearDown(): void
  {
    new Filesystem()->remove($this->directory);
  }

  public function testAFileThatCouldNotBeScannedIsSkippedAndTheImportReportsFailure(): void
  {
    $project = new Project();
    $project->setName('fine');
    $manager = $this->createStub(RemixManipulationProjectManager::class);
    $manager->method('addProject')->willReturnCallback(
      static fn (AddProjectRequest $request): Project => str_contains($request->getProjectFile()->getFilename(), 'unscanned')
        ? throw new MalwareScanUnavailableException('Malware scan could not run: ClamAV unavailable') : $project
    );
    $user = new User();
    $user->setUsername('importer');
    $user_manager = $this->createStub(UserManager::class);
    $user_manager->method('findUserByUsername')->willReturn($user);

    $tester = new CommandTester(new ProjectImportCommand($user_manager, $manager));
    $exit_code = $tester->execute(['directory' => $this->directory, 'user' => 'importer']);

    $this->assertSame(1, $exit_code, 'an import with unscanned files must not report success');
    $this->assertStringContainsString('FAILED to add program: Malware scan could not run', $tester->getDisplay());
    $this->assertStringContainsString('Added program <fine>', $tester->getDisplay(), 'the other file is still imported');
  }
}
