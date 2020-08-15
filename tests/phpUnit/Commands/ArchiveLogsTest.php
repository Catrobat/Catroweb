<?php

namespace Tests\phpUnit\Admin;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CleanupTest.
 *
 * @internal
 * @covers \App\Commands\Maintenance\ArchiveLogsCommand
 */
class ArchiveLogsTest extends KernelTestCase
{
  /**
   * @test
   */
  public function archiveLogs(): void
  {
    // setup app
    $kernel = static::createKernel();
    $application = new Application($kernel);
    $command = $application->find('catrobat:logs:archive');

    $log_dir = $kernel->getContainer()->getParameter('catrobat.logs.dir');

    // create test log folder under testdata -> we don't want to remove our real logs
    if (!file_exists($log_dir))
    {
      mkdir($log_dir);
    }

    // run command
    $commandTester = new CommandTester($command);
    $return = $commandTester->execute([]);
    $this->assertEquals(0, $return);

    // check if directory is not empty
    $this->assertNotEmpty(array_diff(scandir($log_dir), ['.', '..', '.gitignore']),
            'No file in log directory got archived.');
  }
}
