<?php
/**
 * Created by PhpStorm.
 * User: catroweb
 * Date: 11/8/17
 * Time: 7:23 PM
 */

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class DroneMoveUpBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class DroneMoveUpBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::AR_DRONE_MOVE_UP_BRICK;
    $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);

    $this->caption = "MOVE the drone UP for " . $formulas[Constants::AR_DRONE_TIME_TO_FLY_IN_SECONDS]
      . " seconds with " . $formulas[Constants::AR_DRONE_POWER_IN_PERCENT] . "% power";

    $this->setImgFile(Constants::AR_DRONE_BRICK_IMG);
  }
}