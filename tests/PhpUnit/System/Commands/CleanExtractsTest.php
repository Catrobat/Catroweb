<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands;

use App\System\Commands\Maintenance\CleanExtractsCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(CleanExtractsCommand::class)]
class CleanExtractsTest extends KernelTestCase
{
  private CommandTester $command_tester;

  private string $extract_dir;

  private Filesystem $filesystem;

  #[\Override]
  protected function setUp(): void
  {
    $kernel = static::createKernel();
    $container = static::getContainer();
    $application = new Application($kernel);
    $command = $application->find('catrobat:clean:extracts');
    $this->command_tester = new CommandTester($command);
    $extract_dir_param = $container->getParameter('catrobat.file.extract.dir');
    \assert(\is_string($extract_dir_param));
    $this->extract_dir = $extract_dir_param;
    $this->filesystem = new Filesystem();
  }

  #[\Override]
  protected function tearDown(): void
  {
    // Clean up any test directories we created
    foreach (['old_project_1', 'old_project_2', 'recent_project'] as $dir) {
      $path = $this->extract_dir.$dir;
      if (is_dir($path)) {
        $this->filesystem->remove($path);
      }
    }

    parent::tearDown();
  }

  public function testDeletesOldDirectories(): void
  {
    $old_dir = $this->extract_dir.'old_project_1';
    $this->filesystem->mkdir($old_dir);
    $this->filesystem->touch($old_dir.'/code.xml');
    // Set modification time to 10 days ago
    touch($old_dir, time() - 10 * 86400);

    $recent_dir = $this->extract_dir.'recent_project';
    $this->filesystem->mkdir($recent_dir);
    $this->filesystem->touch($recent_dir.'/code.xml');

    $return = $this->command_tester->execute(['--days' => '7']);

    $this->assertSame(0, $return);
    $this->assertDirectoryDoesNotExist($old_dir);
    $this->assertDirectoryExists($recent_dir);
    $this->assertStringContainsString('Directories deleted: 1', $this->command_tester->getDisplay());
  }

  public function testDryRunDoesNotDelete(): void
  {
    $old_dir = $this->extract_dir.'old_project_2';
    $this->filesystem->mkdir($old_dir);
    $this->filesystem->touch($old_dir.'/code.xml');
    touch($old_dir, time() - 10 * 86400);

    $return = $this->command_tester->execute(['--days' => '7', '--dry-run' => true]);

    $this->assertSame(0, $return);
    $this->assertDirectoryExists($old_dir);
    $this->assertStringContainsString('[DRY RUN]', $this->command_tester->getDisplay());
    $this->assertStringContainsString('Would delete: old_project_2', $this->command_tester->getDisplay());
  }

  public function testRejectsInvalidDays(): void
  {
    $return = $this->command_tester->execute(['--days' => '0']);

    $this->assertSame(1, $return);
    $this->assertStringContainsString('Days must be a positive integer', $this->command_tester->getDisplay());
  }

  public function testNothingToDelete(): void
  {
    $return = $this->command_tester->execute(['--days' => '7']);

    $this->assertSame(0, $return);
    $this->assertStringContainsString('Directories deleted: 0', $this->command_tester->getDisplay());
  }

  public function testCustomDaysOption(): void
  {
    $dir = $this->extract_dir.'old_project_1';
    $this->filesystem->mkdir($dir);
    $this->filesystem->touch($dir.'/code.xml');
    // Set modification time to 2 days ago
    touch($dir, time() - 2 * 86400);

    // Should NOT delete with 7-day threshold
    $return = $this->command_tester->execute(['--days' => '7']);
    $this->assertSame(0, $return);
    $this->assertDirectoryExists($dir);

    // Should delete with 1-day threshold
    $return = $this->command_tester->execute(['--days' => '1']);
    $this->assertSame(0, $return);
    $this->assertDirectoryDoesNotExist($dir);
  }
}
