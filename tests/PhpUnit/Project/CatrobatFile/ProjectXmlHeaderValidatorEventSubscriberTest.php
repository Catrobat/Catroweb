<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\CatrobatFile\ProjectXmlHeaderValidatorEventSubscriber;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ProjectXmlHeaderValidatorEventSubscriber::class)]
class ProjectXmlHeaderValidatorEventSubscriberTest extends TestCase
{
  private ProjectXmlHeaderValidatorEventSubscriber $program_xml_header_validator;

  #[\Override]
  protected function setUp(): void
  {
    $this->program_xml_header_validator = new ProjectXmlHeaderValidatorEventSubscriber();
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProjectXmlHeaderValidatorEventSubscriber::class, $this->program_xml_header_validator);
  }

  /**
   * @throws Exception
   */
  public function testChecksIfTheProgramXmlHeaderIsValid(): void
  {
    $file = $this->createMock(ExtractedCatrobatFile::class);
    $xml = simplexml_load_file(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/code.xml');
    $this->assertInstanceOf(\SimpleXMLElement::class, $xml);
    $file->expects($this->atLeastOnce())->method('getProjectXmlProperties')->willReturn($xml);
    $this->program_xml_header_validator->validate($file);
  }

  /**
   * @throws Exception
   */
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

  /**
   * @throws Exception
   */
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

  /**
   * @throws Exception
   */
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

  /**
   * @throws Exception
   */
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
