<?php

namespace Catrobat\AppBundle\Spec\Listeners;

use Catrobat\AppBundle\Model\ExtractedCatrobatFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class LicenseUpdaterSpec extends ObjectBehavior
{

  function it_is_initializable()
  {
      $this->shouldHaveType('Catrobat\AppBundle\Listeners\LicenseUpdater');
  }

  function it_sets_media_license()
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(__SPEC_GENERATED_FIXTURES_DIR__."/base/", __SPEC_CACHE_DIR__."/base/" );
    $xml = simplexml_load_file(__SPEC_CACHE_DIR__."/base/code.xml");
    expect($xml->header->mediaLicense)->toBeLike("");
    $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__."/base/");
    $this->update($file);
    $xml = simplexml_load_file(__SPEC_CACHE_DIR__."/base/code.xml");
    expect($xml->header->mediaLicense)->toBeLike("http://developer.catrobat.org/ccbysa_v4");
  }

  function it_sets_program_license()
  {
    $filesystem = new Filesystem();
    $filesystem->mirror(__SPEC_GENERATED_FIXTURES_DIR__."/base/", __SPEC_CACHE_DIR__."/base/" );
    $xml = simplexml_load_file(__SPEC_CACHE_DIR__."/base/code.xml");
    expect($xml->header->programLicense)->toBeLike("");
    $file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__."/base/");
    $this->update($file);
    $xml = simplexml_load_file(__SPEC_CACHE_DIR__."/base/code.xml");
    expect($xml->header->programLicense)->toBeLike("http://developer.catrobat.org/agpl_v3");
  }
}
