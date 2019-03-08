<?php

namespace tests\PhpSpec\spec\App\Catrobat\Listeners;

use App\Catrobat\Services\ExtractedCatrobatFile;
use PhpSpec\ObjectBehavior;

class ProgramXmlHeaderValidatorSpec extends ObjectBehavior
{
  public function it_is_initializable()
  {
    $this->shouldHaveType('App\Catrobat\Listeners\ProgramXmlHeaderValidator');
  }

  public function it_checks_if_the_program_xml_header_is_valid(ExtractedCatrobatFile $file)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__ . '/base/code.xml');
    $file->getProgramXmlProperties()->willReturn($xml);
    $this->shouldNotThrow('App\Catrobat\Exceptions\Upload\InvalidXmlException')->duringValidate($file);
  }

  public function it_throws_an_exception_if_header_is_missing(ExtractedCatrobatFile $file)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__ . '/base/code.xml');
    unset($xml->header);
    $file->getProgramXmlProperties()->willReturn($xml);
    $this->shouldThrow('App\Catrobat\Exceptions\Upload\InvalidXmlException')->duringValidate($file);
  }

  public function it_throws_an_exception_if_header_information_is_missing(ExtractedCatrobatFile $file)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__ . '/base/code.xml');
    unset($xml->header->applicationName);
    $file->getProgramXmlProperties()->willReturn($xml);
    $this->shouldThrow('App\Catrobat\Exceptions\Upload\InvalidXmlException')->duringValidate($file);
  }

  public function it_checks_if_program_name_is_set(ExtractedCatrobatFile $file)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__ . '/base/code.xml');
    unset($xml->header->programName);
    $file->getProgramXmlProperties()->willReturn($xml);
    $this->shouldThrow('App\Catrobat\Exceptions\Upload\InvalidXmlException')->duringValidate($file);
  }

  public function it_checks_if_description_is_set(ExtractedCatrobatFile $file)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__ . '/base/code.xml');
    unset($xml->header->description);
    $file->getProgramXmlProperties()->willReturn($xml);
    $this->shouldThrow('App\Catrobat\Exceptions\Upload\InvalidXmlException')->duringValidate($file);
  }
}
