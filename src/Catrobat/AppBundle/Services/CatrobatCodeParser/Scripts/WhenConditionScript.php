<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class WhenConditionScript
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts
 */
class WhenConditionScript extends Script
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::WHEN_CONDITION_SCRIPT;
    $this->caption = "When "
      . FormulaResolver::resolve($this->script_xml_properties->formulaMap)[Constants::IF_CONDITION_FORMULA]
      . " becomes true";

    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}