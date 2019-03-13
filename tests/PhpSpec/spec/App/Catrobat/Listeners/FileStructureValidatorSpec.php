<?php

namespace tests\PhpSpec\spec\App\Catrobat\Listeners;

use App\Catrobat\Services\ExtractedCatrobatFile;
use PhpSpec\ObjectBehavior;

class FileStructureValidatorSpec extends ObjectBehavior
{
  public function it_is_initializable()
  {
    $this->shouldHaveType('App\Catrobat\Listeners\FileStructureValidator');
  }

  public function it_makes_sure_the_program_has_a_valid_file_structure(ExtractedCatrobatFile $file)
  {
    $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__ . '/base');
    $this->shouldNotThrow('App\Catrobat\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }
}
