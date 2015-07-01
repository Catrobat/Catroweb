<?php

namespace Catrobat\AppBundle\Spec\Listeners;

use PhpSpec\ObjectBehavior;

class OnlyDefinedImagesValidatorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Listeners\OnlyDefinedImagesValidator');
    }

    /**
     * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
     */
    public function it_makes_sure_only_images_defined_in_the_xml_are_in_the_image_directory($file)
    {
        $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__.'/base');
        $file->getProgramXmlProperties()->willReturn(simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__.'/base/code.xml'));
        $this->shouldNotThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }

    /**
     * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
     */
    public function it_throws_an_exception_if_there_is_an_image_not_specified_in_xml($file)
    {
        $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__.'/program_with_extra_image');

        $file->getProgramXmlProperties()->willReturn(simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__.'/program_with_extra_image/code.xml'));
        $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }

    /**
     * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
     */
    public function it_throws_an_exception_if_a_image_is_missing($file)
    {
        $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__.'/program_with_missing_image');

        $file->getProgramXmlProperties()->willReturn(simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__.'/program_with_missing_image/code.xml'));
        $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
}
