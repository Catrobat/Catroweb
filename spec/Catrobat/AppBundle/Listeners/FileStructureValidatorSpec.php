<?php

namespace spec\Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use PhpSpec\ObjectBehavior;

class FileStructureValidatorSpec extends ObjectBehavior
{
  public function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\AppBundle\Listeners\FileStructureValidator');
  }

  public function it_makes_sure_the_program_has_a_valid_file_structure(ExtractedCatrobatFile $file)
  {
    $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__ . '/base');
    $this->shouldNotThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }
}
