<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\DB\EntityRepository\Project\ProgramRepository;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\Remix\RemixData;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 *
 * @covers  \App\Project\CatrobatFile\ExtractedCatrobatFile
 */
class ExtractedCatrobatFileTest extends TestCase
{
  private ExtractedCatrobatFile $extracted_catrobat_file;

  protected function setUp(): void
  {
    $this->extracted_catrobat_file = new ExtractedCatrobatFile(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/', '/webpath', 'hash');
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

  public function testGetsTheProgramCreditsFromXml(): void
  {
    $this->assertSame('', $this->extracted_catrobat_file->getNotesAndCredits());
  }

  /**
   * @throws \Exception
   *
   * @psalm-suppress InvalidPropertyFetch
   */
  public function testSetsTheProgramName(): void
  {
    $target_dir = BootstrapExtension::$CACHE_DIR.'base/';
    $filesystem = new Filesystem();
    $filesystem->mirror(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/', $target_dir);

    $new_name = 'new_name';

    $this->extracted_catrobat_file = new ExtractedCatrobatFile($target_dir, '/webpath', 'hash');
    $this->extracted_catrobat_file->setName($new_name);
    $this->extracted_catrobat_file->saveProjectXmlProperties();

    $content = file_get_contents(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $xml = @simplexml_load_string($content);
    $this->assertSame($new_name, (string) $xml->header->programName);
  }

  /**
   * @throws \Exception
   *
   * @psalm-suppress InvalidPropertyFetch
   */
  public function testSetsTheProgramDescription(): void
  {
    $target_dir = BootstrapExtension::$CACHE_DIR.'base/';
    $filesystem = new Filesystem();
    $filesystem->mirror(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/', $target_dir);

    $new_description = 'new_description';

    $this->extracted_catrobat_file = new ExtractedCatrobatFile($target_dir, '/webpath', 'hash');
    $this->extracted_catrobat_file->setDescription($new_description);
    $this->extracted_catrobat_file->saveProjectXmlProperties();

    $content = file_get_contents(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $xml = @simplexml_load_string($content);
    $this->assertSame($new_description, (string) $xml->header->description);
  }

  /**
   * @throws \Exception
   *
   * @psalm-suppress InvalidPropertyFetch
   */
  public function testSetsTheProgramCredits(): void
  {
    $target_dir = BootstrapExtension::$CACHE_DIR.'base/';
    $filesystem = new Filesystem();
    $filesystem->mirror(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/', $target_dir);

    $new_credits = 'new_credits';

    $this->extracted_catrobat_file = new ExtractedCatrobatFile($target_dir, '/webpath', 'hash');
    $this->extracted_catrobat_file->setNotesAndCredits($new_credits);
    $this->extracted_catrobat_file->saveProjectXmlProperties();

    $content = file_get_contents(BootstrapExtension::$CACHE_DIR.'base/code.xml');
    $xml = @simplexml_load_string($content);
    $this->assertSame($new_credits, (string) $xml->header->notesAndCredits);
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

    $expectedCount = 2;
    $expectedProgramIds = ['117697631', '3570'];
    $expectedUrls = [$first_expected_url, $second_expected_url];
    $this->assertions($expectedCount, $urls, $expectedUrls, $expectedProgramIds, [true, false], [true, false]);

    /*
     $this->assertCount(2, $urls);
    $this->assertInstanceOf(RemixData::class, $urls[0]);
    $this->assertSame($first_expected_url, $urls[0]->getUrl());
    $this->assertSame('117697631', $urls[0]->getProjectId());
    $this->assertTrue($urls[0]->isScratchProject());
    $this->assertTrue($urls[0]->isAbsoluteUrl());

    $this->assertInstanceOf(RemixData::class, $urls[1]);
    $this->assertSame($second_expected_url, $urls[1]->getUrl());
    $this->assertSame('3570', $urls[1]->getProjectId());
    $this->assertFalse($urls[1]->isScratchProject());
    $this->assertFalse($urls[1]->isAbsoluteUrl());
     */
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testCanExtractSimpleCatrobatAbsoluteRemixUrl(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://pocketcode.org/details/1234/';
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $first_expected_url;
    $new_program_id = '1300';
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);

    $this->assertions(1, $urls, [$first_expected_url], ['1234'], [false], [true]);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testNotExtractNumberFromNormalText(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'SomeText 123';
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $first_expected_url;
    $new_program_id = '124';
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);
    $this->assertCount(0, $urls);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testCanExtractSimpleScratchAbsoluteRemixUrl(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $first_expected_url;
    $new_program_id = '1';
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);

    $this->assertions(1, $urls, [$first_expected_url], ['117697631'], [true], [true]);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testCanExtractSimpleRelativeCatrobatRemixUrl(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = '/app/flavors/3570/';
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $first_expected_url;
    $new_program_id = '6310';
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);

    $this->assertions(1, $urls, [$first_expected_url], ['3570'], [false], [false]);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testCanExtractMergedProgramRemixUrls(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/3570/';
    $new_program_id = '3571';
    $remixes_string = 'スーパー時計 12 ['.$first_expected_url.'], The Periodic Table 2 ['.$second_expected_url.']]';
    /* @psalm-suppress UndefinedPropertyAssignment */
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);

    $this->assertions(2, $urls, [$first_expected_url, $second_expected_url], ['1234', '3570'], [false, false], [true, true]);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testExtractUniqueProgramRemixUrls(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/1234/';
    $new_program_id = '3571';
    $remixes_string = 'スーパー時計 12 ['.$first_expected_url.'], The Periodic Table 2 ['.$second_expected_url.']]';
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);

    $this->assertions(1, $urls, [$first_expected_url, $second_expected_url], ['1234'], [false], [true]);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testDontExtractProgramRemixUrlsReferencingToCurrentProgram(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/790/';
    $new_program_id = '1234';
    $remixes_string = 'スーパー時計 12 ['.$first_expected_url.'], The Periodic Table 2 ['.$second_expected_url.']]';
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);

    $this->assertions(1, $urls, [$second_expected_url], ['790'], [false], [true]);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  public function testExtractOnlyOlderProgramRemixUrls(): void
  {
    $program_repository = $this->createMock(ProgramRepository::class);
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/790/';
    $new_program_id = '791';
    $remixes_string = 'スーパー時計 12 ['.$first_expected_url.'], The Periodic Table 2 ['.$second_expected_url.']]';
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);

    $this->assertions(1, $urls, [$second_expected_url], ['790'], [false], [true]);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
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
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);

    $this->assertions(3, $urls, [$first_expected_url, $second_expected_url, $third_expected_url],
      ['1234', '3570', '121648946'], [false, false, true], [true, true, true]);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
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
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);

    $this->assertions(2, $urls, [$first_expected_url, $second_expected_url],
      ['1234', '121648946'], [false, true], [true, true]);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
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
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);

    $this->assertions(1, $urls, [$first_expected_url],
      ['1234'], [false], [true]);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
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
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);

    $this->assertions(4, $urls, [$first_expected_url, $second_expected_url, $third_expected_url, $fourth_expected_url],
      ['117697631', '3570', '121648946', '16267'], [true, false, true, false], [true, false, true, true]);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
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
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);

    $this->assertions(2, $urls, [$first_expected_url, $second_expected_url],
      ['117697631', '16267'], [true, false], [true, false]);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
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
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, true, $program_repository);

    $this->assertions(1, $urls, [$first_expected_url],
      ['117697631'], [true], [true]);
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
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
    $this->extracted_catrobat_file->getProjectXmlProperties()->header->url = $remixes_string;
    $urls = $this->extracted_catrobat_file->getRemixesData($new_program_id, false, $program_repository);

    $this->assertions(2, $urls, [$first_expected_url, $fourth_expected_url],
      ['117697631', '16268'], [true, false], [true, true]);
  }

  public function testReturnsThePathOfTheBaseDirectory(): void
  {
    $this->assertSame(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/', $this->extracted_catrobat_file->getPath());
  }

  public function testReturnsTheXmlProperties(): void
  {
    $this->assertInstanceOf(\SimpleXMLElement::class, $this->extracted_catrobat_file->getProjectXmlProperties());
  }

  public function testReturnsThePathOfTheAutomaticScreenshot(): void
  {
    $this->assertSame(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/automatic_screenshot.png', $this->extracted_catrobat_file->getScreenshotPath());
  }

  public function testReturnsThePathOfTheManualScreenshot(): void
  {
    $this->extracted_catrobat_file = new ExtractedCatrobatFile(BootstrapExtension::$GENERATED_FIXTURES_DIR.'program_with_manual_screenshot/', '/webpath', 'hash');
    $this->assertSame(BootstrapExtension::$GENERATED_FIXTURES_DIR.'program_with_manual_screenshot/manual_screenshot.png', $this->extracted_catrobat_file->getScreenshotPath());
  }

  public function testReturnsThePathOfTheScreenshot(): void
  {
    $this->extracted_catrobat_file = new ExtractedCatrobatFile(BootstrapExtension::$GENERATED_FIXTURES_DIR.'program_with_screenshot/', '/webpath', 'hash');
    $this->assertSame(BootstrapExtension::$GENERATED_FIXTURES_DIR.'program_with_screenshot/screenshot.png', $this->extracted_catrobat_file->getScreenshotPath());
  }

  public function testThrowsAnExceptionWhenCodeXmlIsMissing(): void
  {
    $this->expectException(InvalidCatrobatFileException::class);
    $this->extracted_catrobat_file->__construct(BootstrapExtension::$GENERATED_FIXTURES_DIR.'program_with_missing_code_xml/', '', '');
  }

  public function testThrowsAnExceptionWhenCodeXmlIsInvalid(): void
  {
    $this->expectException(InvalidCatrobatFileException::class);
    $this->extracted_catrobat_file->__construct(BootstrapExtension::$GENERATED_FIXTURES_DIR.'program_with_invalid_code_xml/', '', '');
  }

  public function testIgnoresAnInvalid0XmlChar(): void
  {
    $this->extracted_catrobat_file = new ExtractedCatrobatFile(BootstrapExtension::$FIXTURES_DIR.'program_with_0_xmlchar/', '/webpath', 'hash');
    $this->assertInstanceOf(\SimpleXMLElement::class, $this->extracted_catrobat_file->getProjectXmlProperties());
  }

  public function testPreservesInvalid0XmlCharFromCollisionsWithOtherActors(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(BootstrapExtension::$FIXTURES_DIR.'program_with_0_xmlchar/', BootstrapExtension::$CACHE_DIR.'program_with_0_xmlchar/');

    $base_xml_string = file_get_contents(BootstrapExtension::$CACHE_DIR.'program_with_0_xmlchar/code.xml');
    $count = substr_count($base_xml_string, '<receivedMessage>cupcake2&lt;&#x0;-&#x0;&gt;cupcake4</receivedMessage>');
    Assert::assertEquals($count, 1);

    $this->extracted_catrobat_file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'program_with_0_xmlchar/', '/webpath', 'hash');
    $this->assertInstanceOf(\SimpleXMLElement::class, $this->extracted_catrobat_file->getProjectXmlProperties());
    $this->extracted_catrobat_file->saveProjectXmlProperties();

    $base_xml_string = file_get_contents(BootstrapExtension::$CACHE_DIR.'program_with_0_xmlchar/code.xml');
    $count = substr_count($base_xml_string, '<receivedMessage>cupcake2&lt;&#x0;-&#x0;&gt;cupcake4</receivedMessage>');
    Assert::assertEquals($count, 1);
  }

  public function testPreservesInvalid0XmlCharFromCollisionsWithAnything(): void
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(BootstrapExtension::$FIXTURES_DIR.'/program_with_0_xmlchar/', BootstrapExtension::$CACHE_DIR.'program_with_0_xmlchar/');

    $base_xml_string = file_get_contents(BootstrapExtension::$CACHE_DIR.'program_with_0_xmlchar/code.xml');
    $count = substr_count($base_xml_string, '<receivedMessage>cupcake4&lt;&#x0;-&#x0;&gt;&#x0;ANYTHING&#x0;</receivedMessage>');
    Assert::assertEquals($count, 1);

    $this->extracted_catrobat_file = new ExtractedCatrobatFile(BootstrapExtension::$CACHE_DIR.'program_with_0_xmlchar/', '/webpath', 'hash');
    $this->assertInstanceOf(\SimpleXMLElement::class, $this->extracted_catrobat_file->getProjectXmlProperties());
    $this->extracted_catrobat_file->saveProjectXmlProperties();

    $base_xml_string = file_get_contents(BootstrapExtension::$CACHE_DIR.'program_with_0_xmlchar/code.xml');
    $count = substr_count($base_xml_string, '<receivedMessage>cupcake4&lt;&#x0;-&#x0;&gt;&#x0;ANYTHING&#x0;</receivedMessage>');
    Assert::assertEquals($count, 1);
  }

  public function testIOSXMLVersionWorksWithCurrentRegex(): void
  {
    $this->extracted_catrobat_file = new ExtractedCatrobatFile(BootstrapExtension::$FIXTURES_DIR.'program_with_old_XML/', '/webpath', 'hash');
    Assert::assertTrue($this->extracted_catrobat_file->isFileMentionedInXml('1f363a1435a9497852285dbfa82b74e4_Background.png'));
    Assert::assertTrue($this->extracted_catrobat_file->isFileMentionedInXml('4728a2ce6b682ac056b8f8185353108d_Moving Mole.png'));
    Assert::assertTrue($this->extracted_catrobat_file->isFileMentionedInXml('1fb4ecf442b988ad20279d95acaf608e_Whacked Mole.png'));
    Assert::assertTrue($this->extracted_catrobat_file->isFileMentionedInXml('0370b09e8cd2cd025397a47e24b129d5_Hit2.m4a'));
  }

  /**
   * @psalm-suppress UndefinedPropertyAssignment
   */
  private function assertions(int $expectedCount, array $urls, array $expectedURLs, array $expectedProgramIds, array $scratch, array $absolutePaths): void
  {
    $this->assertCount($expectedCount, $urls);

    for ($i = 0; $i < $expectedCount; ++$i) {
      $this->assertInstanceOf(RemixData::class, $urls[$i]);
      $this->assertSame($expectedURLs[$i], $urls[$i]->getUrl());
      $this->assertSame($expectedProgramIds[$i], $urls[$i]->getProjectId());
      if ($scratch[$i]) {
        $this->assertTrue($urls[$i]->isScratchProject());
      } else {
        $this->assertFalse($urls[$i]->isScratchProject());
      }
      if ($absolutePaths[$i]) {
        $this->assertTrue($urls[$i]->isAbsoluteUrl());
      } else {
        $this->assertFalse($urls[$i]->isAbsoluteUrl());
      }
    }
  }
}
