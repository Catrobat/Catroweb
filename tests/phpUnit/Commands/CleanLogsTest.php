<?php

namespace Tests\phpUnit\Admin;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CleanupTest.
 *
 * @internal
 * @covers \App\Commands\Maintenance\CleanLogsCommand
 */
class CleanLogsTest extends KernelTestCase
{
  /**
   * @test
   */
  public function cleanLogs(): void
  {
    // setup app
    $kernel = static::createKernel();
    $application = new Application($kernel);
    $command = $application->find('catrobat:clean:logs');

    $log_dir = $kernel->getContainer()->getParameter('catrobat.logs.dir');

    // create test log folder under testdata -> we don't want to remove our real logs
    if (!file_exists($log_dir))
    {
      mkdir($log_dir);
    }

    // fill directory
    $test_log_dir = $log_dir.'test';
    if (!file_exists($test_log_dir))
    {
      mkdir($test_log_dir);
    }

    for ($i = 0; $i < 10; ++$i)
    {
      $filename = uniqid('', true);
      $tmp_file = fopen($test_log_dir.DIRECTORY_SEPARATOR.$filename, 'w');
      fclose($tmp_file);
    }

    for ($i = 0; $i < 4; ++$i)
    {
      $filename = uniqid('', true);
      $tmp_file = fopen($log_dir.$filename, 'w');
      fclose($tmp_file);
    }

    // run command
    $commandTester = new CommandTester($command);
    $return = $commandTester->execute([]);
    $this->assertEquals(0, $return);

    // check if directory is empty
    $this->assertEmpty(array_diff(scandir($log_dir), ['.', '..', '.gitignore']),
            'Not all files in log directory got deleted.');
  }
}
