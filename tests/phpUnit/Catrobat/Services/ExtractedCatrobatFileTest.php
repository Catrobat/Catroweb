<?php

namespace Tests\phpUnit\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RemixData;
use App\Repository\ProgramRepository;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use Symfony\Component\Filesystem\Filesystem;
use Tests\phpUnit\Hook\RefreshTestEnvHook;

/**
 * @internal
 * @covers  \App\Catrobat\Services\ExtractedCatrobatFile
 */
class ExtractedCatrobatFileTest extends TestCase
{
  private ExtractedCatrobatFile $extracted_catrobat_file;

  protected function setUp(): void
  {
    $this->extracted_catrobat_file = new ExtractedCatrobatFile(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/', '/webpath', 'hash');
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ExtractedCatrobatFile::class, $this->extracted_catrobat_file);
  }

  public function testGetsTheProgramNameFromXml(): void
  {
    $this->assertSame('test', $this->extracted_catrobat_file->getName());
  }

  public function testGetsTheProgramDescriptionFromXml(): void
  {
    $this->assertSame('', $this->extracted_catrobat_file->getDescription());
  }

  public function testGetsTheLanguageVersionFromXml(): void
  {
    $this->assertSame('0.92', $this->extracted_catrobat_file->getLanguageVersion());
  }

  public function testGetsTheApplicationVersionFromXml(): void
  {
    $this->assertSame('0.9.7', $this->extracted_catrobat_file->getApplicationVersion());
  }

  public function testGetsTheRemixUrlsStringFromXml(): void
  {
    $this->assertSame('やねうら部屋(びっくりハウス) remix お化け屋敷 [https://scratch.mit.edu/projects/117697631/], '
      .'The Periodic Table [/app/project/3570]', $this->extracted_catrobat_file->getRemixUrlsString());
  }

  public function testGetsRelativeAndAbsoluteRemixUrls(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $second_expected_url = '/app/project/3570';
    $new_program_id = '3571';
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(2, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('117697631', $urls[0]->getProgramId());
    $this->assertTrue($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
    $this->assertInstanceOf(RemixData::class, $urls[1]);
    $this->assertSame($second_expected_url, $urls[1]->getUrl());
    $this->assertSame('3570', $urls[1]->getProgramId());
    $this->assertFalse($urls[1]->isScratchProgram());
    $this->assertFalse($urls[1]->isAbsoluteUrl());
  }

  public function testCanExtractSimpleCatrobatAbsoluteRemixUrl(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://pocketcode.org/details/1234/';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $first_expected_url;
    $new_program_id = '1300';
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(1, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('1234', $urls[0]->getProgramId());
    $this->assertFalse($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
  }

  public function testNotExtractNumberFromNormalText(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'SomeText 123';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $first_expected_url;
    $new_program_id = '124';
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(0, $urls);
  }

  public function testCanExtractSimpleScratchAbsoluteRemixUrl(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $first_expected_url;
    $new_program_id = '1';
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(1, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('117697631', $urls[0]->getProgramId());
    $this->assertTrue($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
  }

  public function testCanExtractSimpleRelativeCatrobatRemixUrl(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = '/app/flavors/3570/';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $first_expected_url;
    $new_program_id = '6310';
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(1, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('3570', $urls[0]->getProgramId());
    $this->assertFalse($urls[0]->isScratchProgram());
    $this->assertFalse($urls[0]->isAbsoluteUrl());
  }

  public function testCanExtractMergedProgramRemixUrls(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/3570/';
    $new_program_id = '3571';
    $remixes_string = 'スーパー時計 12 ['.$first_expected_url.'], The Periodic Table 2 ['.$second_expected_url.']]';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(2, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('1234', $urls[0]->getProgramId());
    $this->assertFalse($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
    $this->assertInstanceOf(RemixData::class, $urls[1]);
    $this->assertSame($second_expected_url, $urls[1]->getUrl());
    $this->assertSame('3570', $urls[1]->getProgramId());
    $this->assertFalse($urls[1]->isScratchProgram());
    $this->assertTrue($urls[1]->isAbsoluteUrl());
  }

  public function testExtractUniqueProgramRemixUrls(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/1234/';
    $new_program_id = '3571';
    $remixes_string = 'スーパー時計 12 ['.$first_expected_url.'], The Periodic Table 2 ['.$second_expected_url.']]';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(1, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('1234', $urls[0]->getProgramId());
    $this->assertFalse($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
  }

  public function testDontExtractProgramRemixUrlsReferencingToCurrentProgram(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/790/';
    $new_program_id = '1234';
    $remixes_string = 'スーパー時計 12 ['.$first_expected_url.'], The Periodic Table 2 ['.$second_expected_url.']]';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(1, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($second_expected_url, $urls[0]->getUrl());
    $this->assertSame('790', $urls[0]->getProgramId());
    $this->assertFalse($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
  }

  public function testExtractOnlyOlderProgramRemixUrls(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/790/';
    $new_program_id = '791';
    $remixes_string = 'スーパー時計 12 ['.$first_expected_url.'], The Periodic Table 2 ['.$second_expected_url.']]';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(1, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($second_expected_url, $urls[0]->getUrl());
    $this->assertSame('790', $urls[0]->getProgramId());
    $this->assertFalse($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
  }

  public function testCanExtractDoubleMergedProgramRemixUrls(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/3570/';
    $third_expected_url = 'https://scratch.mit.edu/projects/121648946/';
    $new_program_id = '3571';
    $remixes_string = 'スーパー時計 12 ['.$first_expected_url
        .'], いやいや棒 [ 01 やねうら部屋(びっくりハウス) remix お化け屋敷 ['.$second_expected_url
        .'], The Periodic Table 2 ['.$third_expected_url.']]';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(3, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('1234', $urls[0]->getProgramId());
    $this->assertFalse($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
    $this->assertInstanceOf(RemixData::class, $urls[1]);
    $this->assertSame($second_expected_url, $urls[1]->getUrl());
    $this->assertSame('3570', $urls[1]->getProgramId());
    $this->assertFalse($urls[1]->isScratchProgram());
    $this->assertTrue($urls[1]->isAbsoluteUrl());
    $this->assertInstanceOf(RemixData::class, $urls[2]);
    $this->assertSame($third_expected_url, $urls[2]->getUrl());
    $this->assertSame('121648946', $urls[2]->getProgramId());
    $this->assertTrue($urls[2]->isScratchProgram());
    $this->assertTrue($urls[2]->isAbsoluteUrl());
  }

  public function testExtractUniqueProgramRemixUrlsOfDoubleMergedProgram(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'https://scratch.mit.edu/projects/121648946/';
    $third_expected_url = 'http://pocketcode.org/details/1234/';
    $new_program_id = '3571';
    $remixes_string = 'スーパー時計 12 ['.$first_expected_url
        .'], いやいや棒 [ 01 やねうら部屋(びっくりハウス) remix お化け屋敷 ['.$second_expected_url
        .'], The Periodic Table 2 ['.$third_expected_url.']]';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(2, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('1234', $urls[0]->getProgramId());
    $this->assertFalse($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
    $this->assertInstanceOf(RemixData::class, $urls[1]);
    $this->assertSame($second_expected_url, $urls[1]->getUrl());
    $this->assertSame('121648946', $urls[1]->getProgramId());
    $this->assertTrue($urls[1]->isScratchProgram());
    $this->assertTrue($urls[1]->isAbsoluteUrl());
  }

  public function testDontExtractProgramRemixUrlsReferencingToCurrentDoubleMergedProgram(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/7901';
    $third_expected_url = 'http://pocketcode.org/details/1234/';
    $new_program_id = '7901';
    $remixes_string = 'スーパー時計 12 ['.$first_expected_url
        .'], いやいや棒 [ 01 やねうら部屋(びっくりハウス) remix お化け屋敷 ['.$second_expected_url
        .'], The Periodic Table 2 ['.$third_expected_url.']]';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(1, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('1234', $urls[0]->getProgramId());
    $this->assertFalse($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
  }

  public function testCanExtractMultipleMergedRemixUrls(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $second_expected_url = '/pocketalice/project/3570';
    $third_expected_url = 'https://scratch.mit.edu/projects/121648946/';
    $fourth_expected_url = 'https://share.catrob.at/app/project/16267';
    $new_program_id = '16268';
    $remixes_string = 'いやいや棒 12 [いやいや棒 9010~(89) [やねうら部屋(びっくりハウス) remix お化け屋敷 ['.$first_expected_url
        .'], The 12 Periodic Table 234 ['.$second_expected_url.']], スーパー時計 ['.$third_expected_url
        .']], NYAN CAT RUNNER (BETA) ['.$fourth_expected_url.']';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(4, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('117697631', $urls[0]->getProgramId());
    $this->assertTrue($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
    $this->assertInstanceOf(RemixData::class, $urls[1]);
    $this->assertSame($second_expected_url, $urls[1]->getUrl());
    $this->assertSame('3570', $urls[1]->getProgramId());
    $this->assertFalse($urls[1]->isScratchProgram());
    $this->assertFalse($urls[1]->isAbsoluteUrl());
    $this->assertInstanceOf(RemixData::class, $urls[2]);
    $this->assertSame($third_expected_url, $urls[2]->getUrl());
    $this->assertSame('121648946', $urls[2]->getProgramId());
    $this->assertTrue($urls[2]->isScratchProgram());
    $this->assertTrue($urls[2]->isAbsoluteUrl());
    $this->assertInstanceOf(RemixData::class, $urls[3]);
    $this->assertSame($fourth_expected_url, $urls[3]->getUrl());
    $this->assertSame('16267', $urls[3]->getProgramId());
    $this->assertFalse($urls[3]->isScratchProgram());
    $this->assertTrue($urls[3]->isAbsoluteUrl());
  }

  public function testExtractUniqueProgramRemixUrlsOfMultipleMergedProgram(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $second_expected_url = '/pocketalice/project/16267';
    $third_expected_url = $first_expected_url;
    $fourth_expected_url = 'https://share.catrob.at/app/project/16267';
    $new_program_id = '16268';
    $remixes_string = 'いやいや棒 12 [いやいや棒 9010~(89) [やねうら部屋(びっくりハウス) remix お化け屋敷 ['.$first_expected_url
        .'], The 12 Periodic Table 234 ['.$second_expected_url.']], スーパー時計 ['.$third_expected_url
        .']], NYAN CAT RUNNER (BETA) ['.$fourth_expected_url.']';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(2, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('117697631', $urls[0]->getProgramId());
    $this->assertTrue($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
    $this->assertInstanceOf(RemixData::class, $urls[1]);
    $this->assertSame($second_expected_url, $urls[1]->getUrl());
    $this->assertSame('16267', $urls[1]->getProgramId());
    $this->assertFalse($urls[1]->isScratchProgram());
    $this->assertFalse($urls[1]->isAbsoluteUrl());
  }

  public function testExtractOnlyOlderProgramRemixUrlsOfMultipleMergedProgramIfItIsAnInitialVersion(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $second_expected_url = '/pocketalice/project/16268';
    $third_expected_url = $first_expected_url;
    $fourth_expected_url = 'https://share.catrob.at/app/project/16268';
    $new_program_id = '16267';
    $remixes_string = 'いやいや棒 12 [いやいや棒 9010~(89) [やねうら部屋(びっくりハウス) remix お化け屋敷 ['.$first_expected_url
        .'], The 12 Periodic Table 234 ['.$second_expected_url.']], スーパー時計 ['.$third_expected_url
        .']], NYAN CAT RUNNER (BETA) ['.$fourth_expected_url.']';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(1, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('117697631', $urls[0]->getProgramId());
    $this->assertTrue($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
  }

  public function testExtractOlderProgramRemixUrlsOfMultipleMergedProgramIfItIsNotAnInitialVersion(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $second_expected_url = '/pocketalice/project/16267';
    $third_expected_url = $first_expected_url;
    $fourth_expected_url = 'https://share.catrob.at/app/project/16268';
    $new_program_id = '16267';
    $remixes_string = 'いやいや棒 12 [いやいや棒 9010~(89) [やねうら部屋(びっくりハウス) remix お化け屋敷 ['.$first_expected_url
        .'], The 12 Periodic Table 234 ['.$second_expected_url.']], スーパー時計 ['.$third_expected_url
        .']], NYAN CAT RUNNER (BETA) ['.$fourth_expected_url.']';
    $this->extracted_catrobat_file->getProgramXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, false, $program_repository);
    $this->assertCount(2, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('117697631', $urls[0]->getProgramId());
    $this->assertTrue($urls[0]->isScratchProgram());
    $this->assertTrue($urls[0]->isAbsoluteUrl());
    $this->assertInstanceOf(RemixData::class, $urls[1]);
    $this->assertSame($fourth_expected_url, $urls[1]->getUrl());
    $this->assertSame('16268', $urls[1]->getProgramId());
    $this->assertFalse($urls[1]->isScratchProgram());
    $this->assertTrue($urls[1]->isAbsoluteUrl());
  }

  public function testReturnsThePathOfTheBaseDirectory(): void
  {
    $this->assertSame(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/', $this->extracted_catrobat_file->getPath());
  }

  public function testReturnsTheXmlProperties(): void
  {
    $this->assertInstanceOf(SimpleXMLElement::class, $this->extracted_catrobat_file->getProgramXmlProperties());
  }

  public function testReturnsThePathOfTheAutomaticScreenshot(): void
  {
    $this->assertSame(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/automatic_screenshot.png', $this->extracted_catrobat_file->getScreenshotPath());
  }

  public function testReturnsThePathOfTheManualScreenshot(): void
  {
    $this->extracted_catrobat_file = new ExtractedCatrobatFile(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'program_with_manual_screenshot/', '/webpath', 'hash');
    $this->assertSame(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'program_with_manual_screenshot/manual_screenshot.png', $this->extracted_catrobat_file->getScreenshotPath());
  }

  public function testReturnsThePathOfTheScreenshot(): void
  {
    $this->extracted_catrobat_file = new ExtractedCatrobatFile(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'program_with_screenshot/', '/webpath', 'hash');
    $this->assertSame(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'program_with_screenshot/screenshot.png', $this->extracted_catrobat_file->getScreenshotPath());
  }

  public function testThrowsAnExceptionWhenCodeXmlIsMissing(): void
  {
    $this->expectException(InvalidCatrobatFileException::class);
    $this->extracted_catrobat_file->__construct(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'program_with_missing_code_xml/', '', '');
  }

  public function testThrowsAnExceptionWhenCodeXmlIsInvalid(): void
  {
    $this->expectException(InvalidCatrobatFileException::class);
    $this->extracted_catrobat_file->__construct(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'program_with_invalid_code_xml/', '', '');
  }

  public function testIgnoresAnInvalid0XmlChar(): void
  {
    $this->extracted_catrobat_file = new ExtractedCatrobatFile(RefreshTestEnvHook::$FIXTURES_DIR.'program_with_0_xmlchar/', '/webpath', 'hash');
    $this->assertInstanceOf(SimpleXMLElement::class, $this->extracted_catrobat_file->getProgramXmlProperties());
  }

  public function testPreservesInvalid0XmlCharFromCollisionsWithOtherActors(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(RefreshTestEnvHook::$FIXTURES_DIR.'program_with_0_xmlchar/', RefreshTestEnvHook::$CACHE_DIR.'program_with_0_xmlchar/');

    $base_xml_string = file_get_contents(RefreshTestEnvHook::$CACHE_DIR.'program_with_0_xmlchar/code.xml');
    $count = substr_count($base_xml_string, '<receivedMessage>cupcake2&lt;&#x0;-&#x0;&gt;cupcake4</receivedMessage>');
    Assert::assertEquals($count, 1);

    $this->extracted_catrobat_file = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'/program_with_0_xmlchar/', '/webpath', 'hash');
    $this->assertInstanceOf(SimpleXMLElement::class, $this->extracted_catrobat_file->getProgramXmlProperties());
    $this->extracted_catrobat_file->saveProgramXmlProperties();

    $base_xml_string = file_get_contents(RefreshTestEnvHook::$CACHE_DIR.'/program_with_0_xmlchar/code.xml');
    $count = substr_count($base_xml_string, '<receivedMessage>cupcake2&lt;&#x0;-&#x0;&gt;cupcake4</receivedMessage>');
    Assert::assertEquals($count, 1);
  }

  public function testPreservesInvalid0XmlCharFromCollisionsWithAnything(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(RefreshTestEnvHook::$FIXTURES_DIR.'/program_with_0_xmlchar/', RefreshTestEnvHook::$CACHE_DIR.'/program_with_0_xmlchar/');

    $base_xml_string = file_get_contents(RefreshTestEnvHook::$CACHE_DIR.'/program_with_0_xmlchar/code.xml');
    $count = substr_count($base_xml_string, '<receivedMessage>cupcake4&lt;&#x0;-&#x0;&gt;&#x0;ANYTHING&#x0;</receivedMessage>');
    Assert::assertEquals($count, 1);

    $this->extracted_catrobat_file = new ExtractedCatrobatFile(RefreshTestEnvHook::$CACHE_DIR.'/program_with_0_xmlchar/', '/webpath', 'hash');
    $this->assertInstanceOf(SimpleXMLElement::class, $this->extracted_catrobat_file->getProgramXmlProperties());
    $this->extracted_catrobat_file->saveProgramXmlProperties();

    $base_xml_string = file_get_contents(RefreshTestEnvHook::$CACHE_DIR.'/program_with_0_xmlchar/code.xml');
    $count = substr_count($base_xml_string, '<receivedMessage>cupcake4&lt;&#x0;-&#x0;&gt;&#x0;ANYTHING&#x0;</receivedMessage>');
    Assert::assertEquals($count, 1);
  }
}
