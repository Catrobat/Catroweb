<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands;

use App\Storage\FileHelper;
use App\System\Testing\DataFixtures\DataBaseUtils;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ClearCompressedProjectsTest.
 *
 * @internal
 *
 * @covers \App\System\Commands\Maintenance\CleanCompressedProjectsCommand
 */
class ClearCompressedProjectsTest extends KernelTestCase
{
  private CommandTester $command_tester;

  private string $compressed_projects_dir;

  protected function setUp(): void
  {
    $kernel = static::createKernel();
    $application = new Application($kernel);
    $command = $application->find('catrobat:clean:compressed');
    $this->command_tester = new CommandTester($command);

    $this->compressed_projects_dir = (string) $kernel->getContainer()->getParameter('catrobat.file.storage.dir');

    // create dir if not exists
    if (!file_exists($this->compressed_projects_dir)) {
      mkdir($this->compressed_projects_dir);
    }

    $this->clearCompressedProjectsDir();
  }

  public function testClearExtractedProjectsShouldRemoveAllUnusedData(): void
  {
    $this->generateUnusedData();

    $return = $this->command_tester->execute([]);
    $this->assertEquals(0, $return);

    // check if directory is empty
    $this->assertEmpty(array_diff(scandir($this->compressed_projects_dir), ['.', '..', '.gitignore']),
      'Not all files in log directory got deleted.');
  }

  public function testClearExtractedProjectsShouldRemoveAllData(): void
  {
    $this->generateUnusedData();

    $return = $this->command_tester->execute([]);
    $this->assertEquals(0, $return);

    // check if directory is empty
    $this->assertEmpty(array_diff(scandir($this->compressed_projects_dir), ['.', '..', '.gitignore']),
      'Not all files in log directory got deleted.');

    DataBaseUtils::databaseRollback();
  }

  private function clearCompressedProjectsDir(): void
  {
    FileHelper::emptyDirectory($this->compressed_projects_dir);
  }

  private function generateUnusedData(): void
  {
    for ($i = 0; $i < 15; ++$i) {
      touch($this->compressed_projects_dir.DIRECTORY_SEPARATOR.$i.'.catrobat');
    }
  }
}
