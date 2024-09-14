<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands;

use App\System\Commands\Maintenance\CleanApkCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 */
#[CoversClass(CleanApkCommand::class)]
class CleanApkTest extends KernelTestCase
{
  private CommandTester $command_tester;

  private string $apk_dir;

  #[\Override]
  protected function setUp(): void
  {
    $kernel = static::createKernel();
    $container = static::getContainer();
    $application = new Application($kernel);
    $command = $application->find('catrobat:clean:apk');
    $this->command_tester = new CommandTester($command);
    $this->apk_dir = (string) $container->getParameter('catrobat.apk.dir');
    fopen('/tmp/PhpUnitTestCleanApk', 'w');
    $file = new File('/tmp/PhpUnitTestCleanApk');
    $file->move($this->apk_dir, 'test');
  }

  public function testClearApkData(): void
  {
    $this->assertNotEmpty(array_diff(scandir($this->apk_dir), ['.', '..', '.gitignore']),
      'Apk directory empty.');
    $return = $this->command_tester->execute([]);
    $this->assertEquals(0, $return);
    $this->assertEmpty(array_diff(scandir($this->apk_dir), ['.', '..', '.gitignore']),
      'Not all files in apk directory got deleted.');
  }
}
