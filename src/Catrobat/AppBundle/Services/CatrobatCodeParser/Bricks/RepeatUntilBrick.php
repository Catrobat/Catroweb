<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class RepeatUntilBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class RepeatUntilBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::REPEAT_UNTIL_BRICK;
    $this->caption = "Repeat until "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::REPEAT_UNTIL_CONDITION_FORMULA]
      . " is true";

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}