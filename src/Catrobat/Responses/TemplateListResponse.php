<?php

namespace App\Catrobat\Responses;

class TemplateListResponse
{
  private $templates;

  /**
   * TemplateListResponse constructor.
   *
   * @param mixed $templates
   */
  public function __construct($templates)
  {
    $this->templates = $templates;
  }

  /**
   * @return mixed
   */
  public function getTemplates()
  {
    return $this->templates;
  }
}
