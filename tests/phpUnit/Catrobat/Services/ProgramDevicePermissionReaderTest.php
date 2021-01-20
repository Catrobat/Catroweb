<?php

namespace Tests\phpUnit\Catrobat\Services;

use App\Catrobat\Services\ProgramDevicePermissionReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Tests\phpUnit\Hook\RefreshTestEnvHook;

/**
 * @internal
 * @covers  \App\Catrobat\Services\ProgramDevicePermissionReader
 */
class ProgramDevicePermissionReaderTest extends TestCase
{
  private ProgramDevicePermissionReader $program_device_permission_reader;

  protected function setUp(): void
  {
    $this->program_device_permission_reader = new ProgramDevicePermissionReader();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProgramDevicePermissionReader::class, $this->program_device_permission_reader);
  }

  public function testReturnPermissionsFromACatrobatFilePath(): void
  {
    $filepath = RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'phiro.catrobat';
    $expected = ['TEXT_TO_SPEECH', 'BLUETOOTH_PHIRO', 'VIBRATOR'];
    $this->assertSame($expected, $this->program_device_permission_reader->getPermissions($filepath));
  }

  public function testReturnPermissionsFromACatrobatFile(): void
  {
    $file = new File(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'phiro.catrobat');
    $expected = ['TEXT_TO_SPEECH', 'BLUETOOTH_PHIRO', 'VIBRATOR'];
    $this->assertSame($expected, $this->program_device_permission_reader->getPermissions($file));
  }

  public function testReturnsAnEmptyArrayIfNoPermissionsAreSet(): void
  {
    $filepath = RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base.catrobat';
    $expected = [];
    $this->assertSame($expected, $this->program_device_permission_reader->getPermissions($filepath));
  }
}
