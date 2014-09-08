<?php

namespace Catrobat\CoreBundle\Spec\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ExtractedCatrobatFileSpec extends ObjectBehavior
{
  function let()
  {
    $this->beConstructedWith(__SPEC_GENERATED_FIXTURES_DIR__."base/");
  }
  
  function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\CoreBundle\Model\ExtractedCatrobatFile');
  }

  function it_gets_the_program_name_from_xml()
  {
    $this->getName()->shouldReturn("test");
  }

  function it_gets_the_program_description_from_xml()
  {
    $this->getDescription()->shouldReturn("");
  }
  
  function it_gets_the_language_version_from_xml()
  {
    $this->getLanguageVersion()->shouldReturn("0.92");
  }
  
  function it_gets_the_application_version_from_xml()
  {
    $this->getApplicationVersion()->shouldReturn("0.9.7");
  }
  
  function it_returns_the_path_of_the_base_directory()
  {
    $this->getPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__."base/");
  }
  
  function it_returns_the_xml_properties()
  {
    $this->getProgramXmlProperties()->shouldHaveType('SimpleXMLElement');
  }
  
  function it_returns_the_path_of_the_automatic_screenshot()
  {
    $this->getScreenshotPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__."base/automatic_screenshot.png");
  }
  
  function it_returns_the_path_of_the_manual_screenshot()
  {
    $this->beConstructedWith(__SPEC_GENERATED_FIXTURES_DIR__."program_with_manual_screenshot/");
    $this->getScreenshotPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__."program_with_manual_screenshot/manual_screenshot.png");
  }
  
  function it_returns_the_path_of_the_screenshot()
  {
    $this->beConstructedWith(__SPEC_GENERATED_FIXTURES_DIR__."program_with_screenshot/");
    $this->getScreenshotPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__."program_with_screenshot/screenshot.png");
  }
  
  function it_throws_an_exception_when_code_xml_is_missing()
  {
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->during('__construct', array(__SPEC_GENERATED_FIXTURES_DIR__ . "program_with_missing_code_xml/"));
  }

  function it_throws_an_exception_when_code_xml_is_invalid()
  {
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->during('__construct', array(__SPEC_GENERATED_FIXTURES_DIR__ . "program_with_invalid_code_xml/")); 
  }
  
}
