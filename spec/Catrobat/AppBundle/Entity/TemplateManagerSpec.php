<?php

namespace spec\Catrobat\AppBundle\Entity;

use Catrobat\AppBundle\Entity\Template;
use Catrobat\AppBundle\Entity\TemplateRepository;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\Services\ProgramFileRepository;
use Catrobat\AppBundle\Services\ScreenshotRepository;
use Catrobat\AppBundle\Services\TemplateFileRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Catrobat\AppBundle\Entity\GameJam;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\HttpFoundation\File\File;

class TemplateManagerSpec extends ObjectBehavior
{

  public function let(TemplateFileRepository $file_repository, ScreenshotRepository $screenshot_repository, EntityManager $entity_manager, TemplateRepository $template_repository, Template $template, File $file, File $screenshot, Template $inserted_template)
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

  public function it_saves_template_to_the_file_repository(Template $template, EntityManager $entity_manager, File $file, ProgramFileRepository $file_repository, ClassMetadata $metadata)
  {
    $metadata->getFieldNames()->willReturn(['id']);
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

  public function it_saves_the_screenshots_to_the_screenshot_repository(Template $template, EntityManager $entity_manager, ExtractedCatrobatFile $extracted_file, ScreenshotRepository $screenshot_repository, ClassMetadata $metadata)
  {
    $metadata->getFieldNames()->willReturn(['id']);
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
