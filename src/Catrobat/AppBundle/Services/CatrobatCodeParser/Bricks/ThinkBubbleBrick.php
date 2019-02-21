<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class ThinkBubbleBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class ThinkBubbleBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::THINK_BUBBLE_BRICK;
    $this->caption = "Think \""
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::STRING_FORMULA] . "\"";

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}