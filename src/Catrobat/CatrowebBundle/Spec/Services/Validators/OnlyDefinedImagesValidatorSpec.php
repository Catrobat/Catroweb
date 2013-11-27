<?php

namespace Catrobat\CatrowebBundle\Spec\Services\Validators;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile;

class OnlyDefinedImagesValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\CatrowebBundle\Services\Validators\OnlyDefinedImagesValidator');
    }
    
    /**
     * @param \Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile $file
     */
    function it_makes_sure_only_images_defined_in_the_xml_are_in_the_image_directory($file)
    {
      $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__."/base");
      $file->getProjectXmlProperties()->willReturn(simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/base/code.xml"));
      $this->shouldNotThrow('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
    
    /**
     * @param \Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile $file
     */
    function it_throws_an_exception_if_there_is_an_image_not_specified_in_xml($file)
    {
      $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__."/project_with_extra_image");
      
      $file->getProjectXmlProperties()->willReturn(simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/project_with_extra_image/code.xml"));
      $this->shouldThrow('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
    
    /**
     * @param \Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile $file
     */
    function it_throws_an_exception_if_a_image_is_missing($file)
    {
      $file->getPath()->willReturn(__SPEC_GENERATED_FIXTURES_DIR__."/project_with_missing_image");
    
      $file->getProjectXmlProperties()->willReturn(simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/project_with_missing_image/code.xml"));
      $this->shouldThrow('Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
    }
}
