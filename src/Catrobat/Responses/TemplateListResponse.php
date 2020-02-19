<?php

namespace App\Catrobat\Responses;

/**
 * Class TemplateListResponse.
 */
class TemplateListResponse
{
  /**
   * @var
   */
  private $templates;

  /**
   * TemplateListResponse constructor.
   *
   * @param $templates
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
