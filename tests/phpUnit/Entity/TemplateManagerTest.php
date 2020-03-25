<?php

namespace Tests\phpUnit\Entity;

use App\Catrobat\Services\ScreenshotRepository;
use App\Catrobat\Services\TemplateFileRepository;
use App\Entity\Template;
use App\Entity\TemplateManager;
use App\Repository\TemplateRepository;
use Doctrine\ORM\EntityManager;
use ImagickException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class TemplateManagerTest extends TestCase
{
  private File $file;

  private TemplateManager $templateManager;

  /**
   * @var MockObject|TemplateFileRepository
   */
  private $file_repository;
  /**
   * @var MockObject|ScreenshotRepository
   */
  private $screenshot_repository;
  /**
   * @var EntityManager|MockObject
   */
  private $entity_manager;
  /**
   * @var MockObject|Template
   */
  private $template;

  protected function setUp(): void
  {
    $this->file_repository = $this->createMock(TemplateFileRepository::class);
    $this->screenshot_repository = $this->createMock(ScreenshotRepository::class);
    $this->entity_manager = $this->createMock(EntityManager::class);
    $template_repository = $this->createMock(TemplateRepository::class);
    $this->template = $this->createMock(Template::class);
    $inserted_template = $this->createMock(Template::class);
    $screenshot = $this->createMock(File::class);
    $this->templateManager = new TemplateManager($this->file_repository, $this->screenshot_repository, $this->entity_manager, $template_repository);
    fopen('/tmp/phpUnitTest', 'w');
    $this->file = new File('/tmp/phpUnitTest');
    $this->template->expects($this->any())->method('getLandscapeProgramFile')->willReturn($this->file);
    $this->template->expects($this->any())->method('getId')->willReturn(1);
    $this->template->expects($this->any())->method('getPortraitProgramFile')->willReturn($this->file);
    $screenshot->expects($this->any())->method('getPathname')->willReturn('./path/to/screenshot');
    $this->template->expects($this->any())->method('getThumbnail')->willReturn($screenshot);
    $inserted_template->expects($this->any())->method('getId')->willReturn(1);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(TemplateManager::class, $this->templateManager);
  }

  /**
   * @throws ImagickException
   */
  public function testSavesTemplateToTheFileRepository(): void
  {
    $this->file_repository->expects($this->at(0))->method('saveProgramfile')->with($this->file, 'p_1');
    $this->file_repository->expects($this->at(1))->method('saveProgramfile')->with($this->file, 'l_1');
    $this->templateManager->saveTemplateFiles($this->template);
  }

  /**
   * @throws ImagickException
   */
  public function testSavesTheScreenshotsToTheScreenshotRepository(): void
  {
    $this->screenshot_repository->expects($this->atLeastOnce())->method('saveProgramAssets')->with('./path/to/screenshot', 1);
    $this->templateManager->saveTemplateFiles($this->template);
  }
}
