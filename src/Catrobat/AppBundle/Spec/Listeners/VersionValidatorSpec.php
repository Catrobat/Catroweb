<?php

namespace Catrobat\AppBundle\Spec\Listeners;

use Catrobat\AppBundle\Model\ExtractedCatrobatFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;


class VersionValidatorSpec extends ObjectBehavior
{

  function let($repository, $program_entity, $user)
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(__SPEC_GENERATED_FIXTURES_DIR__."/base/", __SPEC_CACHE_DIR__."/base/" );
  }

  function it_is_initializable()
  {
      $this->shouldHaveType('Catrobat\AppBundle\Listeners\VersionValidator');
  }

  function it_checks_if_the_language_version_is_uptodate()
  {
    $xml = simplexml_load_file(__SPEC_CACHE_DIR__."/base/code.xml");
    $xml->header->catrobatLanguageVersion = "0.92";
    $this->validate($xml);
  }

  function it_throws_an_exception_if_languageversion_is_too_old()
  {
    $xml = simplexml_load_file(__SPEC_CACHE_DIR__."/base/code.xml");
    $xml->header->catrobatLanguageVersion = "0.90";
    $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($xml);
  }


//  /**
//   * @param \Catrobat\AppBundle\Model\ExtractedCatrobatFile $file
//   */
//  function it_throws_nothing_if_a_normal_description_is_validated($file)
//  {
//    $file->getDescription()->willReturn("Hello Text.");
//    $this->shouldNotThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
//  }
//
//  /**
//   * @param \Catrobat\AppBundle\Model\ExtractedCatrobatFile $file
//   */
//  function it_throws_an_exception_if_the_descripiton_contains_a_rude_word($file, $rudewordfilter)
//  {
//    $file->getDescription()->willReturn("rudeword");
//    $rudewordfilter->containsRudeWord(Argument::any())->willReturn(true);
//    $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringValidate($file);
//  }
}
