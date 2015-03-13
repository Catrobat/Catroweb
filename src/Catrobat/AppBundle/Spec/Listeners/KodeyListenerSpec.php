<?php

namespace Catrobat\AppBundle\Spec\Listeners;

use Catrobat\AppBundle\Model\ExtractedCatrobatFile;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class KodeyListenerSpec extends ObjectBehavior
{
  var $file;
  var $permissions_file;
  
  function let()
  {
      $filesystem = new Filesystem();
      $filesystem->mirror(__SPEC_GENERATED_FIXTURES_DIR__."/base/", __SPEC_CACHE_DIR__."/base/" );
      $this->file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__."/base/");
      $this->permissions_file = __SPEC_CACHE_DIR__."/base/" . "/permissions.txt";
  }
  
  function it_is_initializable()
  {
      $this->shouldHaveType('Catrobat\AppBundle\Listeners\KodeyListener');
  }

  /**
   * @param \Catrobat\AppBundle\Entity\Program $program
   */
  function it_flags_a_program_as_kodey_if_kodey_permissions_are_used($program)
  {
    file_put_contents($this->permissions_file, "BLUETOOTH_KODEY\n");
    $this->checkKodey($this->file, $program);
    $program->setKodey(Argument::exact(true))->shouldHaveBeenCalled();
  }

  /**
   * @param \Catrobat\AppBundle\Entity\Program $program
   */
  function it_does_not_flag_a_program_as_kodey_if_no_kodey_permissions_are_used($program)
  {
    file_put_contents($this->permissions_file, "BLUETOOTH_SENSORS_ARDUINO\nTEXT_TO_SPEECH\n");
    $this->checkKodey($this->file, $program);
    $program->setKodey(Argument::exact(true))->shouldNotHaveBeenCalled();
    $program->setKodey(Argument::exact(false))->shouldHaveBeenCalled();
  }
  
  /**
   * @param \Catrobat\AppBundle\Entity\Program $program
   */
  function it_does_not_matter_at_which_line_the_permission_is_set($program)
  {
      file_put_contents($this->permissions_file, "BLUETOOTH_KODEY\nBLUETOOTH_SENSORS_ARDUINO\nTEXT_TO_SPEECH\n");
      $this->checkKodey($this->file, $program);
      $program->setKodey(Argument::exact(true))->shouldHaveBeenCalled();
      file_put_contents($this->permissions_file, "BLUETOOTH_SENSORS_ARDUINO\nTEXT_TO_SPEECH\nBLUETOOTH_KODEY\n");
      $this->checkKodey($this->file, $program);
      $program->setKodey(Argument::exact(true))->shouldHaveBeenCalled();
  }
  
  /**
   * @param \Catrobat\AppBundle\Entity\Program $program
   */
  function it_does_not_flag_a_program_if_there_is_no_permissions_file($program)
  {
      $this->checkKodey($this->file, $program);
      $program->setKodey(Argument::exact(true))->shouldNotHaveBeenCalled();
      $program->setKodey(Argument::exact(false))->shouldHaveBeenCalled();
  }
  
}
