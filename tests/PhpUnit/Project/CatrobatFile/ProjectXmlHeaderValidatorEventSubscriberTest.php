<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CatrobatFile\ProjectXmlHeaderValidatorEventSubscriber;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers  \App\Project\CatrobatFile\ProjectXmlHeaderValidatorEventSubscriber
 */
class ProjectXmlHeaderValidatorEventSubscriberTest extends TestCase
{
  private ProjectXmlHeaderValidatorEventSubscriber $program_xml_header_validator;

  protected function setUp(): void
  {
    $this->program_xml_header_validator = new ProjectXmlHeaderValidatorEventSubscriber();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProjectXmlHeaderValidatorEventSubscriber::class, $this->program_xml_header_validator);
  }

  public function testChecksIfTheProgramXmlHeaderIsValid(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $xml = simplexml_load_file(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    $file->expects($this->atLeastOnce())->method('getProjectXmlProperties')->willReturn($xml);
    $this->program_xml_header_validator->validate($file);
  }

  public function testThrowsAnExceptionIfHeaderIsMissing(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $xml = simplexml_load_file(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    unset($xml->header);
    $file->expects($this->atLeastOnce())->method('getProjectXmlProperties')->willReturn($xml);
    $this->expectException(InvalidCatrobatFileException::class);
    $this->program_xml_header_validator->validate($file);
  }

  public function testThrowsAnExceptionIfHeaderInformationIsMissing(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $xml = simplexml_load_file(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    unset($xml->header->applicationName);
    $file->expects($this->atLeastOnce())->method('getProjectXmlProperties')->willReturn($xml);
    $this->expectException(InvalidCatrobatFileException::class);
    $this->program_xml_header_validator->validate($file);
  }

  public function testChecksIfProgramNameIsSet(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $xml = simplexml_load_file(BootstrapExtension::$GENERATED_FIXTURES_DIR.'/base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    unset($xml->header->programName);
    $file->expects($this->atLeastOnce())->method('getProjectXmlProperties')->willReturn($xml);
    $this->expectException(InvalidCatrobatFileException::class);
    $this->program_xml_header_validator->validate($file);
  }

  public function testChecksIfDescriptionIsSet(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $xml = simplexml_load_file(BootstrapExtension::$GENERATED_FIXTURES_DIR.'/base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    unset($xml->header->description);
    $file->expects($this->atLeastOnce())->method('getProjectXmlProperties')->willReturn($xml);
    $this->expectException(InvalidCatrobatFileException::class);
    $this->program_xml_header_validator->validate($file);
  }
}
