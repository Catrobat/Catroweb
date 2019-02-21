<?php


namespace Catrobat\AppBundle\Responses;


/**
 * Class TemplateListResponse
 * @package Catrobat\AppBundle\Responses
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