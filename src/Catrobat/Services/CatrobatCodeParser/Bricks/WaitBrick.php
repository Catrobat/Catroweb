<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class WaitBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class WaitBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::WAIT_BRICK;
    $this->caption = "Wait "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)
      [Constants::TIME_TO_WAIT_IN_SECONDS_FORMULA] . " second(s)";

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}