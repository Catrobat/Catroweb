<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class WhenConditionScript
 * @package App\Catrobat\Services\CatrobatCodeParser\Scripts
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