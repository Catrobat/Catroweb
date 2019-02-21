<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class ThinkForBubbleBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class ThinkForBubbleBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::THINK_FOR_BUBBLE_BRICK;

    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);
    $this->caption = "Think \"" . $formulas[Constants::STRING_FORMULA] . "\" for "
      . $formulas[Constants::DURATION_IN_SECONDS_FORMULA] . " second/s";

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}