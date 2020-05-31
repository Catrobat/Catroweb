<?php

namespace App\Catrobat\Services;

use App\Entity\Template;
use App\Entity\TemplateManager;
use ImagickException;

class TemplateService
{
  private TemplateManager $templateManager;

  public function __construct(TemplateManager $templateManager)
  {
    $this->templateManager = $templateManager;
  }

  /**
   * @throws ImagickException
   */
  public function saveFiles(Template $template): void
  {
    $this->templateManager->saveTemplateFiles($template);
  }

  /**
   * @param mixed $id
   */
  public function deleteTemplateFiles($id): void
  {
    $this->templateManager->deleteTemplateFiles($id);
  }
}
