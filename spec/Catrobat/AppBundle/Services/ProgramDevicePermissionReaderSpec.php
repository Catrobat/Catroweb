<?php

namespace spec\Catrobat\AppBundle\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\File;

class ProgramDevicePermissionReaderSpec extends ObjectBehavior
{
  function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\AppBundle\Services\ProgramDevicePermissionReader');
  }

  function it_return_permissions_from_a_catrobat_file_path()
  {
    $filepath = __SPEC_GENERATED_FIXTURES_DIR__ . 'phiro.catrobat';
    $expected = ['TEXT_TO_SPEECH', 'BLUETOOTH_PHIRO', 'VIBRATOR'];
    $this->getPermissions($filepath)->shouldReturn($expected);
  }

  function it_return_permissions_from_a_catrobat_file()
  {
    $file = new File(__SPEC_GENERATED_FIXTURES_DIR__ . 'phiro.catrobat');
    $expected = ['TEXT_TO_SPEECH', 'BLUETOOTH_PHIRO', 'VIBRATOR'];
    $this->getPermissions($file)->shouldReturn($expected);
  }

  function it_returns_an_empty_array_if_no_permissions_are_set()
  {
    $filepath = __SPEC_GENERATED_FIXTURES_DIR__ . 'base.catrobat';
    $expected = [];
    $this->getPermissions($filepath)->shouldReturn($expected);
  }
}
