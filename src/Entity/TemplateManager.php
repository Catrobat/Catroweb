<?php

namespace App\Entity;

use App\Catrobat\Services\ScreenshotRepository;
use App\Catrobat\Services\TemplateFileRepository;
use App\Repository\TemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use ImagickException;
use Symfony\Component\HttpFoundation\File\File;

class TemplateManager
{
  const LANDSCAPE_PREFIX = 'l_';
  const PORTRAIT_PREFIX = 'p_';

  protected TemplateFileRepository $file_repository;

  protected ScreenshotRepository $screenshot_repository;

  protected EntityManagerInterface $entity_manager;

  protected TemplateRepository $template_repository;

  public function __construct(TemplateFileRepository $file_repository, ScreenshotRepository $screenshot_repository,
                              EntityManagerInterface $entity_manager, TemplateRepository $template_repository)
  {
    $this->file_repository = $file_repository;
    $this->screenshot_repository = $screenshot_repository;
    $this->entity_manager = $entity_manager;
    $this->template_repository = $template_repository;
  }

  /**
   * @throws ImagickException
   */
  public function saveTemplateFiles(Template $template): void
  {
    if (null !== $template->getId())
    {
      $this->saveThumbnail($template);
      $this->savePortraitProgram($template);
      $this->saveLandscapeProgram($template);
    }
  }

  /**
   * @return mixed
   */
  public function findOneByName(string $templateName)
  {
    return $this->template_repository->findOneBy(['name' => $templateName]);
  }

  /**
   * @return mixed
   */
  public function findAll()
  {
    return $this->template_repository->findAll();
  }

  /**
   * @return mixed
   */
  public function findAllActive()
  {
    return $this->template_repository->findByActive(true);
  }

  public function deleteTemplateFiles(string $id): void
  {
    $this->file_repository->deleteTemplateFiles(self::LANDSCAPE_PREFIX.$id);
    $this->file_repository->deleteTemplateFiles(self::PORTRAIT_PREFIX.$id);
    $this->screenshot_repository->deleteThumbnail($id);
    $this->screenshot_repository->deleteScreenshot($id);
  }

  /**
   * @throws ImagickException
   */
  private function saveThumbnail(Template $template): void
  {
    $file = $template->getThumbnail();
    if (null == $file)
    {
      return;
    }
    $thumbnail = $template->getThumbnail();
    $this->screenshot_repository->saveProgramAssets($thumbnail->getPathname(), (string) $template->getId());
  }

  private function saveLandscapeProgram(Template $template): void
  {
    $file = $template->getLandscapeProgramFile();
    $this->saveTemplateProgram($file, self::LANDSCAPE_PREFIX.$template->getId());
  }

  private function savePortraitProgram(Template $template): void
  {
    $file = $template->getPortraitProgramFile();
    $this->saveTemplateProgram($file, self::PORTRAIT_PREFIX.$template->getId());
  }

  private function saveTemplateProgram(?File $file, string $id): void
  {
    if (null === $file)
    {
      return;
    }
    $this->file_repository->saveProgramFile($file, $id);
  }
}
