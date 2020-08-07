<?php

namespace Tests\phpUnit\Commands;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class ClearExtractedProjectsTest.
 *
 * @internal
 * @covers \App\Commands\Maintenance\CleanApkCommand
 */
class CleanApkTest extends KernelTestCase
{
  private CommandTester $command_tester;

  private string $apk_dir;

  protected function setUp(): void
  {
    $kernel = static::createKernel();
    $application = new Application($kernel);
    $command = $application->find('catrobat:clean:apk');
    $this->command_tester = new CommandTester($command);
    $this->apk_dir = $kernel->getContainer()->getParameter('catrobat.apk.dir');
    fopen('/tmp/phpUnitTestCleanApk', 'w');
    $file = new File('/tmp/phpUnitTestCleanApk');
    $file->move($this->apk_dir, 'test');
  }

  /**
   * @test
   */
  public function clearApkData(): void
  {
    $this->assertNotEmpty(array_diff(scandir($this->apk_dir), ['.', '..', '.gitignore']),
            'Apk directory empty.');
    $return = $this->command_tester->execute([]);
    $this->assertEquals(0, $return);
    $this->assertEmpty(array_diff(scandir($this->apk_dir), ['.', '..', '.gitignore']),
            'Not all files in apk directory got deleted.');
  }
}
