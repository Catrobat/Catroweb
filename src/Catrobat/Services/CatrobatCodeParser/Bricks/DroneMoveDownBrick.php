<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class DroneMoveDownBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class DroneMoveDownBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::AR_DRONE_MOVE_DOWN_BRICK;
    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);

    $this->caption = "MOVE the drone DOWN for " . $formulas[Constants::AR_DRONE_TIME_TO_FLY_IN_SECONDS]
      . " seconds with " . $formulas[Constants::AR_DRONE_POWER_IN_PERCENT] . "% power";

    $this->setImgFile(Constants::AR_DRONE_BRICK_IMG);
  }
}