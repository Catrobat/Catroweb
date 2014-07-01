<?php

namespace Catrobat\CoreBundle\Spec\Listeners;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class OnlyDefinedImagesValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\CoreBundle\Listeners\OnlyDefinedImagesValidator');
    }
    
    /**
     * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
     */
    function it_makes_sure_only_images_defined_in_the_xml_are_in_the_image_directory($file)
    {
      $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__."/base");
      $file->getProgramXmlProperties()->willReturn(simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/base/code.xml"));
      $this->shouldNotThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
    
    /**
     * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
     */
    function it_throws_an_exception_if_there_is_an_image_not_specified_in_xml($file)
    {
      $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__."/program_with_extra_image");
      
      $file->getProgramXmlProperties()->willReturn(simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/program_with_extra_image/code.xml"));
      $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
    
    /**
     * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
     */
    function it_throws_an_exception_if_a_image_is_missing($file)
    {
      $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__."/program_with_missing_image");
    
      $file->getProgramXmlProperties()->willReturn(simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/program_with_missing_image/code.xml"));
      $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
}
