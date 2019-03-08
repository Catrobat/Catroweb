<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 11/8/17
 * Time: 7:43 PM
 */

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class DroneMoveRightBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class DroneMoveRightBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::AR_DRONE_MOVE_RIGHT_BRICK;
    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);

    $this->caption = "MOVE the drone RIGHT for " . $formulas[Constants::AR_DRONE_TIME_TO_FLY_IN_SECONDS]
      . " seconds with " . $formulas[Constants::AR_DRONE_POWER_IN_PERCENT] . "% power";

    $this->setImgFile(Constants::AR_DRONE_BRICK_IMG);
  }
}