<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class WaitUntilBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
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