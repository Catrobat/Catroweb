<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class RepeatBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class RepeatBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::REPEAT_BRICK;
    $this->caption = "Repeat "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::TIMES_TO_REPEAT_FORMULA]
      . " times";

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}