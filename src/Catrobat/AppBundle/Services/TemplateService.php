<?php

namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Entity\Template;
use Catrobat\AppBundle\Entity\TemplateManager;


/**
 * Class TemplateService
 * @package Catrobat\AppBundle\Services
 */
class TemplateService
{

  /* @var $templateManager \Catrobat\AppBundle\Entity\TemplateManager */
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