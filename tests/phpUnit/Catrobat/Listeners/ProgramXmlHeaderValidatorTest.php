<?php

namespace Tests\phpUnit\Catrobat\Listeners;

use App\Catrobat\Exceptions\Upload\InvalidXmlException;
use App\Catrobat\Listeners\ProgramXmlHeaderValidator;
use App\Catrobat\Services\ExtractedCatrobatFile;
use PHPUnit\Framework\TestCase;
use Tests\phpUnit\Hook\RefreshTestEnvHook;

/**
 * @internal
 * @covers  \App\Catrobat\Listeners\ProgramXmlHeaderValidator
 */
class ProgramXmlHeaderValidatorTest extends TestCase
{
  private ProgramXmlHeaderValidator $program_xml_header_validator;

  protected function setUp(): void
  {
    $this->program_xml_header_validator = new ProgramXmlHeaderValidator();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProgramXmlHeaderValidator::class, $this->program_xml_header_validator);
  }

  public function testChecksIfTheProgramXmlHeaderIsValid(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $xml = simplexml_load_file(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/code.xml');
    $file->expects($this->atLeastOnce())->method('getProgramXmlProperties')->willReturn($xml);
    $this->program_xml_header_validator->validate($file);
  }

  public function testThrowsAnExceptionIfHeaderIsMissing(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $xml = simplexml_load_file(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/code.xml');
    unset($xml->header);
    $file->expects($this->atLeastOnce())->method('getProgramXmlProperties')->willReturn($xml);
    $this->expectException(InvalidXmlException::class);
    $this->program_xml_header_validator->validate($file);
  }

  public function testThrowsAnExceptionIfHeaderInformationIsMissing(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $xml = simplexml_load_file(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/code.xml');
    unset($xml->header->applicationName);
    $file->expects($this->atLeastOnce())->method('getProgramXmlProperties')->willReturn($xml);
    $this->expectException(InvalidXmlException::class);
    $this->program_xml_header_validator->validate($file);
  }

  public function testChecksIfProgramNameIsSet(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $xml = simplexml_load_file(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'/base/code.xml');
    unset($xml->header->programName);
    $file->expects($this->atLeastOnce())->method('getProgramXmlProperties')->willReturn($xml);
    $this->expectException(InvalidXmlException::class);
    $this->program_xml_header_validator->validate($file);
  }

  public function testChecksIfDescriptionIsSet(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $xml = simplexml_load_file(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'/base/code.xml');
    unset($xml->header->description);
    $file->expects($this->atLeastOnce())->method('getProgramXmlProperties')->willReturn($xml);
    $this->expectException(InvalidXmlException::class);
    $this->program_xml_header_validator->validate($file);
  }
}
