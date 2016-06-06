<?php

namespace spec\Catrobat\AppBundle\Listeners;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;

class ProgramExtensionListenerSpec extends ObjectBehavior
{
    public $extracted_catrobat_file_with_extensions;
    public $extracted_catrobat_file_without_extensions;

    /**
     * @param \Catrobat\AppBundle\Entity\ExtensionRepository $repo
     * @param \Catrobat\AppBundle\Entity\Extension $extension
     */
    public function let($repo, $extension)
    {
        $extension->getPrefix()->willReturn("PHIRO");
        $repo->findAll()->willReturn(array($extension));
        $this->beConstructedWith($repo);

        $filesystem = new Filesystem();
        $filesystem->mirror(__SPEC_GENERATED_FIXTURES_DIR__.'/program_with_extensions/', __SPEC_CACHE_DIR__.'/program_with_extensions/');
        $filesystem->mirror(__SPEC_GENERATED_FIXTURES_DIR__.'/base/', __SPEC_CACHE_DIR__.'/base/');

        $this->extracted_catrobat_file_without_extensions = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/base/', '', '');
        $this->extracted_catrobat_file_with_extensions = new ExtractedCatrobatFile(__SPEC_CACHE_DIR__.'/program_with_extensions/', '', '');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Listeners\ProgramExtensionListener');
    }

    /**
     * @param \Catrobat\AppBundle\Entity\Program $program
     */
    public function it_flags_a_program_if_extension_bricks_are_used($program, $extension)
    {
        $this->checkExtension($this->extracted_catrobat_file_with_extensions, $program);
        $program->addExtension($extension)->shouldHaveBeenCalled();
    }

    /**
     * @param \Catrobat\AppBundle\Entity\Program $program
     */
    public function it_does_not_flags_a_program_if_no_extension_bricks_are_used($program, $extension)
    {
        $this->checkExtension($this->extracted_catrobat_file_without_extensions, $program);
        $program->addExtension($extension)->shouldNotHaveBeenCalled();
    }
}
