<?php

namespace App\Catrobat\Services;

use App\Entity\Template;
use App\Entity\TemplateManager;


/**
 * Class TemplateService
 * @package App\Catrobat\Services
 */
class TemplateService
{

  /* @var $templateManager \App\Entity\TemplateManager */
  private $templateManager;

  /**
   * TemplateService constructor.
   *
   * @param TemplateManager $templateManager
   */
  public function __construct(TemplateManager $templateManager)
  {
    $this->templateManager = $templateManager;
  }

  /**
   * @param Template $template
   *
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