<?php

namespace Catrobat\AppBundle\Spec\Listeners;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;

class ProgramPermissionsListenerSpec extends ObjectBehavior
{
    var $file;
    var $permissions_file;
    
    function let()
    {
        $filesystem = new Filesystem();
        $filesystem->mirror(__SPEC_GENERATED_FIXTURES_DIR__."/base/", __SPEC_CACHE_DIR__."/base/" );
        $this->file = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__."/base/","","");
        $this->permissions_file = __SPEC_CACHE_DIR__."/base/" . "/permissions.txt";
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Listeners\ProgramPermissionsListener');
    }
    
    /**
     * @param \Catrobat\AppBundle\Entity\Program $program
     */
    function it_flags_a_program_as_phiro_if_phiro_permissions_are_used($program)
    {
        file_put_contents($this->permissions_file, "BLUETOOTH_PHIRO\n");
        $this->checkPermissions($this->file, $program);
        $program->setPhiro(Argument::exact(true))->shouldHaveBeenCalled();
    }
    
    /**
     * @param \Catrobat\AppBundle\Entity\Program $program
     */
    function it_does_not_flag_a_program_as_phiro_if_no_phiro_permissions_are_used($program)
    {
        file_put_contents($this->permissions_file, "BLUETOOTH_SENSORS_ARDUINO\nTEXT_TO_SPEECH\n");
        $this->checkPermissions($this->file, $program);
        $program->setPhiro(Argument::exact(true))->shouldNotHaveBeenCalled();
        $program->setPhiro(Argument::exact(false))->shouldHaveBeenCalled();
    }
    
    /**
     * @param \Catrobat\AppBundle\Entity\Program $program
     * @param \Catrobat\AppBundle\Entity\Program $program2
     */
    function it_does_not_matter_at_which_line_the_permission_is_set($program, $program2)
    {
        file_put_contents($this->permissions_file, "BLUETOOTH_PHIRO\nBLUETOOTH_SENSORS_ARDUINO\nTEXT_TO_SPEECH\n");
        $this->checkPermissions($this->file, $program);
        $program->setPhiro(Argument::exact(true))->shouldHaveBeenCalled();
        
        file_put_contents($this->permissions_file, "BLUETOOTH_SENSORS_ARDUINO\nTEXT_TO_SPEECH\nBLUETOOTH_PHIRO\n");
        $this->checkPermissions($this->file, $program2);
        $program2->setPhiro(Argument::exact(true))->shouldHaveBeenCalled();
    }
    
    
    /**
     * @param \Catrobat\AppBundle\Entity\Program $program
     */
    function it_flags_a_program_as_lego_if_lego_permissions_are_used($program)
    {
        file_put_contents($this->permissions_file, "BLUETOOTH_LEGO_NXT\n");
        $this->checkPermissions($this->file, $program);
        $program->setLego(Argument::exact(true))->shouldHaveBeenCalled();
    }
    
    
    /**
     * @param \Catrobat\AppBundle\Entity\Program $program
     */
    function it_does_not_flag_a_program_if_there_is_no_permissions_file($program)
    {
        $this->checkPermissions($this->file, $program);
        $program->setPhiro(Argument::exact(true))->shouldNotHaveBeenCalled();
        $program->setPhiro(Argument::exact(false))->shouldHaveBeenCalled();
        $program->setLego(Argument::exact(true))->shouldNotHaveBeenCalled();
    }
    
}
