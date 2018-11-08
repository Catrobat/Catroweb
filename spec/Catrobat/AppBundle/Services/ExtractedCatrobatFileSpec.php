<?php

namespace spec\Catrobat\AppBundle\Services;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Filesystem\Filesystem;


class ExtractedCatrobatFileSpec extends ObjectBehavior
{
  public function let()
  {
    $this->beConstructedWith(__SPEC_GENERATED_FIXTURES_DIR__ . 'base/', '/webpath', 'hash');
  }

  public function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\AppBundle\Services\ExtractedCatrobatFile');
  }

  public function it_gets_the_program_name_from_xml()
  {
    $this->getName()->shouldReturn('test');
  }

  public function it_gets_the_program_description_from_xml()
  {
    $this->getDescription()->shouldReturn('');
  }

  public function it_gets_the_language_version_from_xml()
  {
    $this->getLanguageVersion()->shouldReturn('0.92');
  }

  public function it_gets_the_application_version_from_xml()
  {
    $this->getApplicationVersion()->shouldReturn('0.9.7');
  }

  public function it_gets_the_remix_urls_string_from_xml()
  {
    $this->getRemixUrlsString()->shouldReturn('やねうら部屋(びっくりハウス) remix お化け屋敷 '
      . '[https://scratch.mit.edu/projects/117697631/], '
      . 'The Periodic Table [/pocketcode/program/3570]');
  }

  public function it_gets_relative_and_absolute_remix_urls()
  {
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $second_expected_url = '/pocketcode/program/3570';
    $new_program_id = 3571;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(2);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($first_expected_url);
    $urls[0]->getProgramId()->shouldReturn(117697631);
    $urls[0]->isScratchProgram()->shouldReturn(true);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);

    $urls[1]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[1]->getUrl()->shouldReturn($second_expected_url);
    $urls[1]->getProgramId()->shouldReturn(3570);
    $urls[1]->isScratchProgram()->shouldReturn(false);
    $urls[1]->isAbsoluteUrl()->shouldReturn(false);
  }

  public function it_can_extract_simple_catrobat_absolute_remix_url()
  {
    $first_expected_url = 'https://pocketcode.org/details/1234/';
    $this->getProgramXmlProperties()->header->url = $first_expected_url;
    $new_program_id = 1300;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(1);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($first_expected_url);
    $urls[0]->getProgramId()->shouldReturn(1234);
    $urls[0]->isScratchProgram()->shouldReturn(false);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);
  }

  public function it_should_not_extract_number_from_normal_text()
  {
    $first_expected_url = 'SomeText 123';
    $this->getProgramXmlProperties()->header->url = $first_expected_url;
    $new_program_id = 124;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(0);
  }

  public function it_can_extract_simple_scratch_absolute_remix_url()
  {
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $this->getProgramXmlProperties()->header->url = $first_expected_url;
    $new_program_id = 1;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(1);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($first_expected_url);
    $urls[0]->getProgramId()->shouldReturn(117697631);
    $urls[0]->isScratchProgram()->shouldReturn(true);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);
  }

  public function it_can_extract_simple_relative_catrobat_remix_url()
  {
    $first_expected_url = '/pocketcode/flavors/3570/';
    $this->getProgramXmlProperties()->header->url = $first_expected_url;
    $new_program_id = 6310;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(1);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($first_expected_url);
    $urls[0]->getProgramId()->shouldReturn(3570);
    $urls[0]->isScratchProgram()->shouldReturn(false);
    $urls[0]->isAbsoluteUrl()->shouldReturn(false);
  }

  public function it_can_extract_merged_program_remix_urls()
  {
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/3570/';
    $new_program_id = 3571;

    $remixes_string = 'スーパー時計 12 [' . $first_expected_url . '], The Periodic Table 2 [' . $second_expected_url . ']]';

    $this->getProgramXmlProperties()->header->url = $remixes_string;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(2);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($first_expected_url);
    $urls[0]->getProgramId()->shouldReturn(1234);
    $urls[0]->isScratchProgram()->shouldReturn(false);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);

    $urls[1]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[1]->getUrl()->shouldReturn($second_expected_url);
    $urls[1]->getProgramId()->shouldReturn(3570);
    $urls[1]->isScratchProgram()->shouldReturn(false);
    $urls[1]->isAbsoluteUrl()->shouldReturn(true);
  }

  public function it_extract_unique_program_remix_urls()
  {
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/1234/';
    $new_program_id = 3571;

    $remixes_string = 'スーパー時計 12 [' . $first_expected_url . '], The Periodic Table 2 [' . $second_expected_url . ']]';

    $this->getProgramXmlProperties()->header->url = $remixes_string;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(1);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($first_expected_url);
    $urls[0]->getProgramId()->shouldReturn(1234);
    $urls[0]->isScratchProgram()->shouldReturn(false);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);
  }

  public function it_dont_extract_program_remix_urls_referencing_to_current_program()
  {
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/790/';
    $new_program_id = 1234;

    $remixes_string = 'スーパー時計 12 [' . $first_expected_url . '], The Periodic Table 2 [' . $second_expected_url . ']]';

    $this->getProgramXmlProperties()->header->url = $remixes_string;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(1);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($second_expected_url);
    $urls[0]->getProgramId()->shouldReturn(790);
    $urls[0]->isScratchProgram()->shouldReturn(false);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);
  }

  public function it_extract_only_older_program_remix_urls()
  {
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/790/';
    $new_program_id = 791;

    $remixes_string = 'スーパー時計 12 [' . $first_expected_url . '], The Periodic Table 2 [' . $second_expected_url . ']]';

    $this->getProgramXmlProperties()->header->url = $remixes_string;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(1);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($second_expected_url);
    $urls[0]->getProgramId()->shouldReturn(790);
    $urls[0]->isScratchProgram()->shouldReturn(false);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);
  }

  public function it_can_extract_double_merged_program_remix_urls()
  {
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/3570/';
    $third_expected_url = 'https://scratch.mit.edu/projects/121648946/';
    $new_program_id = 3571;

    $remixes_string = 'スーパー時計 12 [' . $first_expected_url
      . '], いやいや棒 [ 01 やねうら部屋(びっくりハウス) remix お化け屋敷 [' . $second_expected_url
      . '], The Periodic Table 2 [' . $third_expected_url . ']]';

    $this->getProgramXmlProperties()->header->url = $remixes_string;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(3);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($first_expected_url);
    $urls[0]->getProgramId()->shouldReturn(1234);
    $urls[0]->isScratchProgram()->shouldReturn(false);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);

    $urls[1]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[1]->getUrl()->shouldReturn($second_expected_url);
    $urls[1]->getProgramId()->shouldReturn(3570);
    $urls[1]->isScratchProgram()->shouldReturn(false);
    $urls[1]->isAbsoluteUrl()->shouldReturn(true);

    $urls[2]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[2]->getUrl()->shouldReturn($third_expected_url);
    $urls[2]->getProgramId()->shouldReturn(121648946);
    $urls[2]->isScratchProgram()->shouldReturn(true);
    $urls[2]->isAbsoluteUrl()->shouldReturn(true);
  }

  public function it_extract_unique_program_remix_urls_of_double_merged_program()
  {
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'https://scratch.mit.edu/projects/121648946/';
    $third_expected_url = 'http://pocketcode.org/details/1234/';
    $new_program_id = 3571;

    $remixes_string = 'スーパー時計 12 [' . $first_expected_url
      . '], いやいや棒 [ 01 やねうら部屋(びっくりハウス) remix お化け屋敷 [' . $second_expected_url
      . '], The Periodic Table 2 [' . $third_expected_url . ']]';

    $this->getProgramXmlProperties()->header->url = $remixes_string;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(2);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($first_expected_url);
    $urls[0]->getProgramId()->shouldReturn(1234);
    $urls[0]->isScratchProgram()->shouldReturn(false);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);

    $urls[1]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[1]->getUrl()->shouldReturn($second_expected_url);
    $urls[1]->getProgramId()->shouldReturn(121648946);
    $urls[1]->isScratchProgram()->shouldReturn(true);
    $urls[1]->isAbsoluteUrl()->shouldReturn(true);
  }

  public function it_dont_extract_program_remix_urls_referencing_to_current_double_merged_program()
  {
    $first_expected_url = 'https://share2.catrob.at/details/1234';
    $second_expected_url = 'http://pocketcode.org/details/7901';
    $third_expected_url = 'http://pocketcode.org/details/1234/';
    $new_program_id = 7901;

    $remixes_string = 'スーパー時計 12 [' . $first_expected_url
      . '], いやいや棒 [ 01 やねうら部屋(びっくりハウス) remix お化け屋敷 [' . $second_expected_url
      . '], The Periodic Table 2 [' . $third_expected_url . ']]';

    $this->getProgramXmlProperties()->header->url = $remixes_string;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(1);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($first_expected_url);
    $urls[0]->getProgramId()->shouldReturn(1234);
    $urls[0]->isScratchProgram()->shouldReturn(false);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);
  }

  public function it_can_extract_multiple_merged_remix_urls()
  {
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $second_expected_url = '/pocketalice/program/3570';
    $third_expected_url = 'https://scratch.mit.edu/projects/121648946/';
    $fourth_expected_url = 'https://share.catrob.at/pocketcode/program/16267';
    $new_program_id = 16268;

    $remixes_string = 'いやいや棒 12 [いやいや棒 9010~(89) [やねうら部屋(びっくりハウス) remix お化け屋敷 [' . $first_expected_url
      . '], The 12 Periodic Table 234 [' . $second_expected_url . ']], スーパー時計 [' . $third_expected_url
      . ']], NYAN CAT RUNNER (BETA) [' . $fourth_expected_url . ']';

    $this->getProgramXmlProperties()->header->url = $remixes_string;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(4);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($first_expected_url);
    $urls[0]->getProgramId()->shouldReturn(117697631);
    $urls[0]->isScratchProgram()->shouldReturn(true);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);

    $urls[1]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[1]->getUrl()->shouldReturn($second_expected_url);
    $urls[1]->getProgramId()->shouldReturn(3570);
    $urls[1]->isScratchProgram()->shouldReturn(false);
    $urls[1]->isAbsoluteUrl()->shouldReturn(false);

    $urls[2]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[2]->getUrl()->shouldReturn($third_expected_url);
    $urls[2]->getProgramId()->shouldReturn(121648946);
    $urls[2]->isScratchProgram()->shouldReturn(true);
    $urls[2]->isAbsoluteUrl()->shouldReturn(true);

    $urls[3]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[3]->getUrl()->shouldReturn($fourth_expected_url);
    $urls[3]->getProgramId()->shouldReturn(16267);
    $urls[3]->isScratchProgram()->shouldReturn(false);
    $urls[3]->isAbsoluteUrl()->shouldReturn(true);
  }

  public function it_extract_unique_program_remix_urls_of_multiple_merged_program()
  {
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $second_expected_url = '/pocketalice/program/16267';
    $third_expected_url = $first_expected_url;
    $fourth_expected_url = 'https://share.catrob.at/pocketcode/program/16267';
    $new_program_id = 16268;

    $remixes_string = 'いやいや棒 12 [いやいや棒 9010~(89) [やねうら部屋(びっくりハウス) remix お化け屋敷 [' . $first_expected_url
      . '], The 12 Periodic Table 234 [' . $second_expected_url . ']], スーパー時計 [' . $third_expected_url
      . ']], NYAN CAT RUNNER (BETA) [' . $fourth_expected_url . ']';

    $this->getProgramXmlProperties()->header->url = $remixes_string;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(2);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($first_expected_url);
    $urls[0]->getProgramId()->shouldReturn(117697631);
    $urls[0]->isScratchProgram()->shouldReturn(true);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);

    $urls[1]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[1]->getUrl()->shouldReturn($second_expected_url);
    $urls[1]->getProgramId()->shouldReturn(16267);
    $urls[1]->isScratchProgram()->shouldReturn(false);
    $urls[1]->isAbsoluteUrl()->shouldReturn(false);
  }

  public function it_extract_only_older_program_remix_urls_of_multiple_merged_program_if_it_is_an_initial_version()
  {
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $second_expected_url = '/pocketalice/program/16268';
    $third_expected_url = $first_expected_url;
    $fourth_expected_url = 'https://share.catrob.at/pocketcode/program/16268';
    $new_program_id = 16267;

    $remixes_string = 'いやいや棒 12 [いやいや棒 9010~(89) [やねうら部屋(びっくりハウス) remix お化け屋敷 [' . $first_expected_url
      . '], The 12 Periodic Table 234 [' . $second_expected_url . ']], スーパー時計 [' . $third_expected_url
      . ']], NYAN CAT RUNNER (BETA) [' . $fourth_expected_url . ']';

    $this->getProgramXmlProperties()->header->url = $remixes_string;

    $urls = $this->getRemixesData($new_program_id, true);
    $urls->shouldHaveCount(1);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($first_expected_url);
    $urls[0]->getProgramId()->shouldReturn(117697631);
    $urls[0]->isScratchProgram()->shouldReturn(true);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);
  }

  public function it_extract_older_program_remix_urls_of_multiple_merged_program_if_it_is_not_an_initial_version()
  {
    $first_expected_url = 'https://scratch.mit.edu/projects/117697631/';
    $second_expected_url = '/pocketalice/program/16267';
    $third_expected_url = $first_expected_url;
    $fourth_expected_url = 'https://share.catrob.at/pocketcode/program/16268';
    $new_program_id = 16267;

    $remixes_string = 'いやいや棒 12 [いやいや棒 9010~(89) [やねうら部屋(びっくりハウス) remix お化け屋敷 [' . $first_expected_url
      . '], The 12 Periodic Table 234 [' . $second_expected_url . ']], スーパー時計 [' . $third_expected_url
      . ']], NYAN CAT RUNNER (BETA) [' . $fourth_expected_url . ']';

    $this->getProgramXmlProperties()->header->url = $remixes_string;

    $urls = $this->getRemixesData($new_program_id, false);
    $urls->shouldHaveCount(2);

    $urls[0]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[0]->getUrl()->shouldReturn($first_expected_url);
    $urls[0]->getProgramId()->shouldReturn(117697631);
    $urls[0]->isScratchProgram()->shouldReturn(true);
    $urls[0]->isAbsoluteUrl()->shouldReturn(true);

    $urls[1]->shouldBeAnInstanceOf('Catrobat\AppBundle\Services\RemixData');
    $urls[1]->getUrl()->shouldReturn($fourth_expected_url);
    $urls[1]->getProgramId()->shouldReturn(16268);
    $urls[1]->isScratchProgram()->shouldReturn(false);
    $urls[1]->isAbsoluteUrl()->shouldReturn(true);
  }

  public function it_returns_the_path_of_the_base_directory()
  {
    $this->getPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__ . 'base/');
  }

  public function it_returns_the_xml_properties()
  {
    $this->getProgramXmlProperties()->shouldHaveType('SimpleXMLElement');
  }

  public function it_returns_the_path_of_the_automatic_screenshot()
  {
    $this->getScreenshotPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__ . 'base/automatic_screenshot.png');
  }

  public function it_returns_the_path_of_the_manual_screenshot()
  {
    $this->beConstructedWith(__SPEC_GENERATED_FIXTURES_DIR__ . 'program_with_manual_screenshot/', '/webpath', 'hash');
    $this->getScreenshotPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__ . 'program_with_manual_screenshot/manual_screenshot.png');
  }

  public function it_returns_the_path_of_the_screenshot()
  {
    $this->beConstructedWith(__SPEC_GENERATED_FIXTURES_DIR__ . 'program_with_screenshot/', '/webpath', 'hash');
    $this->getScreenshotPath()->shouldReturn(__SPEC_GENERATED_FIXTURES_DIR__ . 'program_with_screenshot/screenshot.png');
  }

  public function it_throws_an_exception_when_code_xml_is_missing()
  {
    $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->during('__construct', [__SPEC_GENERATED_FIXTURES_DIR__ . 'program_with_missing_code_xml/', '/webpath', 'hash']);
  }

  public function it_throws_an_exception_when_code_xml_is_invalid()
  {
    $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->during('__construct', [__SPEC_GENERATED_FIXTURES_DIR__ . 'program_with_invalid_code_xml/', '/webpath', 'hash']);
  }

  public function it_ignores_an_invalid_0_xmlchar()
  {
    $this->beConstructedWith(__SPEC_FIXTURES_DIR__ . 'program_with_0_xmlchar/', '/webpath', 'hash');
    $this->getProgramXmlProperties()->shouldHaveType('SimpleXMLElement');
  }

  public function it_preserves_invalid_0_xmlchar_from_collissions_with_other_actors()
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(__SPEC_FIXTURES_DIR__ . '/program_with_0_xmlchar/', __SPEC_CACHE_DIR__ . '/program_with_0_xmlchar/');

    $base_xml_string = file_get_contents(__SPEC_CACHE_DIR__ . '/program_with_0_xmlchar/code.xml');
    $count = substr_count($base_xml_string, "<receivedMessage>cupcake2&lt;&#x0;-&#x0;&gt;cupcake4</receivedMessage>");
    expect($count)->toBe(1);

    $this->beConstructedWith(__SPEC_CACHE_DIR__ . '/program_with_0_xmlchar/', '/webpath', 'hash');
    $this->getProgramXmlProperties()->shouldHaveType('SimpleXMLElement');
    $this->saveProgramXmlProperties();

    $base_xml_string = file_get_contents(__SPEC_CACHE_DIR__ . '/program_with_0_xmlchar/code.xml');
    $count = substr_count($base_xml_string, "<receivedMessage>cupcake2&lt;&#x0;-&#x0;&gt;cupcake4</receivedMessage>");
    expect($count)->toBe(1);
  }

  public function it_preserves_invalid_0_xmlchar_from_collissions_with_anything()
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(__SPEC_FIXTURES_DIR__ . '/program_with_0_xmlchar/', __SPEC_CACHE_DIR__ . '/program_with_0_xmlchar/');

    $base_xml_string = file_get_contents(__SPEC_CACHE_DIR__ . '/program_with_0_xmlchar/code.xml');
    $count = substr_count($base_xml_string, "<receivedMessage>cupcake4&lt;&#x0;-&#x0;&gt;&#x0;ANYTHING&#x0;</receivedMessage>");
    expect($count)->toBe(1);

    $this->beConstructedWith(__SPEC_CACHE_DIR__ . '/program_with_0_xmlchar/', '/webpath', 'hash');
    $this->getProgramXmlProperties()->shouldHaveType('SimpleXMLElement');
    $this->saveProgramXmlProperties();

    $base_xml_string = file_get_contents(__SPEC_CACHE_DIR__ . '/program_with_0_xmlchar/code.xml');
    $count = substr_count($base_xml_string, "<receivedMessage>cupcake4&lt;&#x0;-&#x0;&gt;&#x0;ANYTHING&#x0;</receivedMessage>");
    expect($count)->toBe(1);
  }

}
