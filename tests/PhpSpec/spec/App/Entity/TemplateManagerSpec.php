<?php

namespace tests\PhpSpec\spec\App\Entity;

use App\Entity\Template;
use App\Repository\TemplateRepository;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\ProgramFileRepository;
use App\Catrobat\Services\ScreenshotRepository;
use App\Catrobat\Services\TemplateFileRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\File;


/**
 * Class TemplateManagerSpec
 * @package tests\PhpSpec\spec\App\Entity
 */
class TemplateManagerSpec extends ObjectBehavior
{

  /**
   * @param TemplateFileRepository|\PhpSpec\Wrapper\Collaborator $file_repository
   * @param ScreenshotRepository|\PhpSpec\Wrapper\Collaborator   $screenshot_repository
   * @param EntityManager|\PhpSpec\Wrapper\Collaborator          $entity_manager
   * @param TemplateRepository|\PhpSpec\Wrapper\Collaborator     $template_repository
   * @param Template|\PhpSpec\Wrapper\Collaborator               $template
   * @param \PhpSpec\Wrapper\Collaborator|File                   $file
   * @param \PhpSpec\Wrapper\Collaborator|File                   $screenshot
   * @param Template|\PhpSpec\Wrapper\Collaborator               $inserted_template
   */
  public function let(TemplateFileRepository $file_repository,
                      ScreenshotRepository $screenshot_repository,
                      EntityManager $entity_manager,
                      TemplateRepository $template_repository,
                      Template $template, File $file,
                      File $screenshot, Template $inserted_template)
  {
    $this->beConstructedWith($file_repository, $screenshot_repository, $entity_manager, $template_repository);

    $template->getLandscapeProgramFile()->willReturn($file);
    $template->getId()->willReturn(1);
    $template->getPortraitProgramFile()->willReturn($file);
    $screenshot->getPathname()->willReturn('./path/to/screenshot');
    $template->getThumbnail()->willReturn($screenshot);
    $inserted_template->getId()->willReturn(1);
  }

  /**
   *
   */
  public function it_is_initializable()
  {
    $this->shouldHaveType('App\Entity\TemplateManager');
  }

  /**
   * @param Template|\PhpSpec\Wrapper\Collaborator               $template
   * @param EntityManager|\PhpSpec\Wrapper\Collaborator          $entity_manager
   * @param \PhpSpec\Wrapper\Collaborator|File                   $file
   * @param TemplateFileRepository|\PhpSpec\Wrapper\Collaborator $file_repository
   * @param ClassMetadata|\PhpSpec\Wrapper\Collaborator          $metadata
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \ImagickException
   */
  public function it_saves_template_to_the_file_repository(Template $template,
                                                           EntityManager $entity_manager,
                                                           File $file,
                                                           TemplateFileRepository $file_repository,
                                                           ClassMetadata $metadata)
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

  /**
   * @param Template|\PhpSpec\Wrapper\Collaborator              $template
   * @param EntityManager|\PhpSpec\Wrapper\Collaborator         $entity_manager
   * @param ExtractedCatrobatFile|\PhpSpec\Wrapper\Collaborator $extracted_file
   * @param ScreenshotRepository|\PhpSpec\Wrapper\Collaborator  $screenshot_repository
   * @param ClassMetadata|\PhpSpec\Wrapper\Collaborator         $metadata
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \ImagickException
   */
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
