<?php

namespace App\Catrobat\Services;

use App\Entity\Template;
use App\Entity\TemplateManager;

/**
 * Class TemplateService.
 */
class TemplateService
{
  /**
   * @var TemplateManager
   */
  private $templateManager;

  /**
   * TemplateService constructor.
   */
  public function __construct(TemplateManager $templateManager)
  {
    $this->templateManager = $templateManager;
  }

  /**
   * @throws \ImagickException
   */
  public function saveFiles(Template $template)
  {
    $this->templateManager->saveTemplateFiles($template);
  }

  /**
   * @param $id
   */
  public function deleteTemplateFiles($id)
  {
    $this->templateManager->deleteTemplateFiles($id);
  }
}
