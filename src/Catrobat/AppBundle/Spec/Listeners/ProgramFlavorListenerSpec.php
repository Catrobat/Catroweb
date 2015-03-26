<?php

namespace Catrobat\AppBundle\Spec\Listeners;

use Catrobat\AppBundle\Model\ExtractedCatrobatFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class ProgramFlavorListenerSpec extends ObjectBehavior
{

  function it_is_initializable()
  {
      $this->shouldHaveType('Catrobat\AppBundle\Listeners\ProgramFlavorListener');
  }

  /**
   * @param \Catrobat\AppBundle\Model\ExtractedCatrobatFile $file
   * @param \Catrobat\AppBundle\Entity\Program $program
   */
  function it_detects_the_pocketcode_flavor($file, $program)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/base/code.xml");
    $xml->header->applicationName = "Pocket Code";
    $file->getProgramXmlProperties()->willReturn($xml);
    $program->setFlavor(Argument::exact('pocketcode'))->shouldBeCalled();
    $this->checkFlavor($file,$program);
  }

  /**
   * @param \Catrobat\AppBundle\Model\ExtractedCatrobatFile $file
   * @param \Catrobat\AppBundle\Entity\Program $program
   */
  function it_detects_the_kodey_flavor($file, $program)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/base/code.xml");
    $xml->header->applicationName = "Pocket Kodey";
    $file->getProgramXmlProperties()->willReturn($xml);
    $program->setFlavor(Argument::exact('pocketkodey'))->shouldBeCalled();
    $this->checkFlavor($file,$program);
  }

  /**
   * @param \Catrobat\AppBundle\Model\ExtractedCatrobatFile $file
   * @param \Catrobat\AppBundle\Entity\Program $program
   */
  function it_throws_an_exception_if_the_flavor_is_unknown($file, $program)
  {
    $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__."/base/code.xml");
    $xml->header->applicationName = "Unknown Pocketcode";
    $file->getProgramXmlProperties()->willReturn($xml);
    $program->setFlavor(Argument::any())->shouldNotBeCalled();
    $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringCheckFlavor($file, $program);
  }

}
