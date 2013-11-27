<?php

namespace Catrobat\CatrowebBundle\Spec\Model;

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
    $this->shouldHaveType('Catrobat\CatrowebBundle\Model\ExtractedCatrobatFile');
  }

  function it_gets_the_project_name_from_xml()
  {
    $this->getName()->shouldReturn("compass");
  }

  function it_gets_the_project_description_from_xml()
  {
    $this->getDescription()->shouldReturn("a simple compass");
  }
  
  function it_gets_the_language_version_from_xml()
  {
    $this->getLanguageVersion()->shouldReturn("0.9");
  }
  
  function it_gets_the_application_version_from_xml()
  {
    $this->getApplicationVersion()->shouldReturn("0.8.5");
  }
  
  function it_returns_the_path_of_the_base_directory()
  {
    $this->getPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__."base/");
  }
  
  function it_returns_the_xml_properties()
  {
    $this->getProjectXmlProperties()->shouldHaveType('SimpleXMLElement');
  }
  
  function it_returns_the_path_of_the_screenshot()
  {
    $this->getScreenshotPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__."base/automatic_screenshot.png");
  }
}
