<?php

namespace Catrobat\CoreBundle\Spec\Listeners;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProgramXmlHeaderValidatorSpec extends ObjectBehavior
{

  function it_is_initializable()
  {
      $this->shouldHaveType('Catrobat\CoreBundle\Listeners\ProgramXmlHeaderValidator');
  }

  /**
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
   */
  function it_checks_if_the_program_xml_header_is_valid($file)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/base/code.xml");
    $file->getProgramXmlProperties()->willReturn($xml);
    $this->shouldNotThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }

  /**
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
   */
  function it_throws_an_exception_if_header_is_missing($file)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/base/code.xml");
    unset($xml->header);
    $file->getProgramXmlProperties()->willReturn($xml);
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }

  /**
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
   */
  function it_throws_an_exception_if_header_information_is_missing($file)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/base/code.xml");
    unset($xml->header->applicationName);
    $file->getProgramXmlProperties()->willReturn($xml);
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }

  /**
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
   */
  function it_checks_if_program_name_is_set($file)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/base/code.xml");
    unset($xml->header->programName);
    $file->getProgramXmlProperties()->willReturn($xml);
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }

  /**
   * @param \Catrobat\CoreBundle\Model\ExtractedCatrobatFile $file
   */
  function it_checks_if_description_is_set($file)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/base/code.xml");
    unset($xml->header->description);
    $file->getProgramXmlProperties()->willReturn($xml);
    $this->shouldThrow('Catrobat\CoreBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
  }
}
