<?php

namespace Catrobat\AppBundle\Spec\Listeners;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileStructureValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Listeners\FileStructureValidator');
    }
    
    /**
     * @param \Catrobat\AppBundle\Model\ExtractedCatrobatFile $file
     */
    function it_makes_sure_the_program_has_a_valid_file_structure($file)
    {
      $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__."/base");
      $this->shouldNotThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
    
    /**
     * @param \Catrobat\AppBundle\Model\ExtractedCatrobatFile $file
     */
    function it_throws_an_exception_if_there_are_too_many_files($file)
    {
      $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__."/program_with_too_many_files");
      $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
    
    /**
     * @param \Catrobat\AppBundle\Model\ExtractedCatrobatFile $file
     */
    function it_throws_an_exception_if_there_are_too_many_folders($file)
    {
      $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__."/program_with_too_many_folders");
      $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
}
