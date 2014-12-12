<?php

namespace Catrobat\AppBundle\Spec\Listeners;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LicenseUpdaterSpec extends ObjectBehavior
{

  function it_is_initializable()
  {
      $this->shouldHaveType('Catrobat\AppBundle\Listeners\LicenseUpdater');
  }


  /**
   * @param \Catrobat\AppBundle\Model\ExtractedCatrobatFile $file
   */
  function it_sets_media_license($file)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/base/code.xml");
    $xml->header->mediaLicense = "";
    $file->getProgramXmlProperties()->willReturn($xml);
    $this->update($file);
    expect($xml->header->mediaLicense)->toBeLike("http://developer.catrobat.org/ccbysa_v4");
  }
}
