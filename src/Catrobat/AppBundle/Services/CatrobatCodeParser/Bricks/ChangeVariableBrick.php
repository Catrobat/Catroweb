<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class ChangeVariableBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::CHANGE_VARIABLE_BRICK;

    $variable = null;
    if ($this->brick_xml_properties->userVariable[Constants::REFERENCE_ATTRIBUTE] != null)
    {
      $variable = (string)$this->brick_xml_properties->userVariable
        ->xpath($this->brick_xml_properties->userVariable[Constants::REFERENCE_ATTRIBUTE])[0];
    }
    else
    {
      $variable = (string)$this->brick_xml_properties->userVariable;
    }
    $this->caption = "Change variable " . $variable . " by "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::VARIABLE_CHANGE_FORMULA];

    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}