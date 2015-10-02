<?php

namespace spec\Catrobat\AppBundle\Listeners;

use PhpSpec\ObjectBehavior;

class FileStructureValidatorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Listeners\FileStructureValidator');
    }

    /**
     * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
     */
    public function it_makes_sure_the_program_has_a_valid_file_structure($file)
    {
        $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__.'/base');
        $this->shouldNotThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }

    /**
     * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
     */
    public function it_throws_an_exception_if_there_are_too_many_files($file)
    {
        $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__.'/program_with_too_many_files');
        $this->shouldThrow('Catrobat\AppBundle\Exceptions\Upload\UnexpectedFileException')->duringValidate($file);
    }

    /**
     * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
     */
    public function it_throws_an_exception_if_there_are_too_many_folders($file)
    {
        $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__.'/program_with_too_many_folders');
        $this->shouldThrow('Catrobat\AppBundle\Exceptions\Upload\UnexpectedFileException')->duringValidate($file);
    }
}
