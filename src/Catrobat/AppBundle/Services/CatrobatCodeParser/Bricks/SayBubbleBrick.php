<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SayBubbleBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class SayBubbleBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SAY_BUBBLE_BRICK;
    $this->caption = "Say \""
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::STRING_FORMULA] . "\"";

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}