<?php

namespace spec\Catrobat\AppBundle\Listeners;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProgramFlavorListenerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Listeners\ProgramFlavorListener');
    }

  /**
   * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
   * @param \Catrobat\AppBundle\Entity\Program $program
   */
  public function it_detects_the_pocketcode_flavor($file, $program)
  {
      $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__.'/base/code.xml');
      $xml->header->applicationName = 'Pocket Code';
      $file->getProgramXmlProperties()->willReturn($xml);
      $program->setFlavor(Argument::exact('pocketcode'))->shouldBeCalled();
      $this->checkFlavor($file, $program);
  }

  /**
   * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
   * @param \Catrobat\AppBundle\Entity\Program $program
   */
  public function it_detects_the_phiropro_flavor($file, $program)
  {
      $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__.'/base/code.xml');
      $xml->header->applicationName = 'Pocket Phiro';
      $file->getProgramXmlProperties()->willReturn($xml);
      $program->setFlavor(Argument::exact('pocketphiropro'))->shouldBeCalled();
      $this->checkFlavor($file, $program);
  }

  /**
   * @param \Catrobat\AppBundle\Services\ExtractedCatrobatFile $file
   * @param \Catrobat\AppBundle\Entity\Program $program
   */
  public function it_throws_an_exception_if_the_flavor_is_unknown($file, $program)
  {
      $xml = simplexml_load_file(__SPEC_GENERATED_FIXTURES_DIR__.'/base/code.xml');
      $xml->header->applicationName = 'Unknown Pocketcode';
      $file->getProgramXmlProperties()->willReturn($xml);
      $program->setFlavor(Argument::any())->shouldNotBeCalled();
      $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException')->duringCheckFlavor($file, $program);
  }
}
