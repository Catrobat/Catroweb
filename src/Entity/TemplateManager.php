<?php

namespace App\Entity;

use App\Catrobat\Services\ScreenshotRepository;
use App\Catrobat\Services\TemplateFileRepository;
use App\Repository\TemplateRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TemplateManager.
 */
class TemplateManager
{
  const LANDSCAPE_PREFIX = 'l_';
  const PORTRAIT_PREFIX = 'p_';

  /**
   * @var TemplateFileRepository
   */
  protected $file_repository;

  /**
   * @var ScreenshotRepository
   */
  protected $screenshot_repository;

  /**
   * @var EntityManagerInterface
   */
  protected $entity_manager;

  /**
   * @var TemplateRepository
   */
  protected $template_repository;

  /**
   * TemplateManager constructor.
   */
  public function __construct(TemplateFileRepository $file_repository, ScreenshotRepository $screenshot_repository,
                              EntityManagerInterface $entity_manager, TemplateRepository $template_repository)
  {
    $this->file_repository = $file_repository;
    $this->screenshot_repository = $screenshot_repository;
    $this->entity_manager = $entity_manager;
    $this->template_repository = $template_repository;
  }

  /**
   * @throws \ImagickException
   */
  public function saveTemplateFiles(Template $template)
  {
    if (null != $template->getId())
    {
      $this->saveThumbnail($template);
      $this->savePortraitProgram($template);
      $this->saveLandscapeProgram($template);
    }
  }

  /**
   * @param $templateName
   *
   * @return mixed
   */
  public function findOneByName($templateName)
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

  /**
   * @param $id
   */
  public function deleteTemplateFiles($id)
  {
    $this->file_repository->deleteTemplateFiles(self::LANDSCAPE_PREFIX.$id);
    $this->file_repository->deleteTemplateFiles(self::PORTRAIT_PREFIX.$id);
    $this->screenshot_repository->deleteThumbnail($id);
    $this->screenshot_repository->deleteScreenshot($id);
  }

  /**
   * @throws \ImagickException
   */
  private function saveThumbnail(Template $template)
  {
    $file = $template->getThumbnail();
    if (null == $file)
    {
      return;
    }
    /* @var $thumbnail \Symfony\Component\HttpFoundation\File\UploadedFile */
    $thumbnail = $template->getThumbnail();
    $this->screenshot_repository->saveProgramAssets($thumbnail->getPathname(), $template->getId());
  }

  private function saveLandscapeProgram(Template $template)
  {
    $file = $template->getLandscapeProgramFile();
    $this->saveTemplateProgram($file, self::LANDSCAPE_PREFIX.$template->getId());
  }

  private function savePortraitProgram(Template $template)
  {
    $file = $template->getPortraitProgramFile();
    $this->saveTemplateProgram($file, self::PORTRAIT_PREFIX.$template->getId());
  }

  /**
   * @param $file
   * @param $id
   */
  private function saveTemplateProgram($file, $id)
  {
    if (null == $file)
    {
      return;
    }
    $this->file_repository->saveProgramFile($file, $id);
  }
}
