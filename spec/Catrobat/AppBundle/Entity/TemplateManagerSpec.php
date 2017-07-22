<?php
namespace spec\Catrobat\AppBundle\Entity;

use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Catrobat\AppBundle\Entity\GameJam;
use Sonata\CoreBundle\Model\Metadata;

class TemplateManagerSpec extends ObjectBehavior
{
    /**
     *
     * @param \Catrobat\AppBundle\Services\TemplateFileRepository $file_repository
     * @param \Catrobat\AppBundle\Services\ScreenshotRepository $screenshot_repository            
     * @param \Catrobat\AppBundle\Entity\TemplateRepository $template_repository
     * @param \Doctrine\ORM\EntityManager $entity_manager
     * @param \Symfony\Component\HttpFoundation\File\File $file
     * @param \Symfony\Component\HttpFoundation\File\File $screenshot
     * @param \Catrobat\AppBundle\Entity\Template $template
     * @param \Catrobat\AppBundle\Entity\Template $inserted_template
     * @param \Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException $validation_exception
     * @param \Doctrine\ORM\Mapping\ClassMetadata $metadata
     */
    public function let($file_repository, $screenshot_repository, $entity_manager, $template_repository, $template, $file, $screenshot, $inserted_template)
    {
        $this->beConstructedWith($file_repository, $screenshot_repository, $entity_manager, $template_repository);

        $template->getLandscapeProgramFile()->willReturn($file);
        $template->getId()->willReturn(1);
        $template->getPortraitProgramFile()->willReturn($file);
        $screenshot->getPathname()->willReturn('./path/to/screenshot');
        $template->getThumbnail()->willReturn($screenshot);
        $inserted_template->getId()->willReturn(1);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Catrobat\AppBundle\Entity\TemplateManager');
    }

    public function it_saves_template_to_the_file_repository($template, $entity_manager, $file, $file_repository, $metadata)
    {
        $metadata->getFieldNames()->willReturn(array('id'));
        $entity_manager->getClassMetadata(Argument::any())->willReturn($metadata);

        $entity_manager->persist(Argument::any())->will(function ($args) {
            $args[0]->setId(1);
            $args[1]->setName('Test');
            return $args[0];
        });

        $this->saveTemplateFiles($template);
        $file_repository->saveProgramfile($file, 'p_1')->shouldHaveBeenCalled();
        $file_repository->saveProgramfile($file, 'l_1')->shouldHaveBeenCalled();
    }
    
    public function it_saves_the_screenshots_to_the_screenshot_repository($template, $entity_manager, $extracted_file, $screenshot_repository, $metadata)
    {
        $metadata->getFieldNames()->willReturn(array('id'));
        $entity_manager->getClassMetadata(Argument::any())->willReturn($metadata);

        $entity_manager->persist(Argument::any())->will(function ($args) {
            $args[0]->setId(1);
            $args[1]->setName('Test');
            return $args[0];
        });
        
        $this->saveTemplateFiles($template);
        $screenshot_repository->saveProgramAssets('./path/to/screenshot', 1)->shouldHaveBeenCalled();
    }

}
