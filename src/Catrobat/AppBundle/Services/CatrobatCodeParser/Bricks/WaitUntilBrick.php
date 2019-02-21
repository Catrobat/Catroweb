<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class WaitUntilBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class WaitUntilBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::WAIT_UNTIL_BRICK;
    $this->caption = "Wait until "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::IF_CONDITION_FORMULA]
      . " is true";

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}