<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class PointInDirectionBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class PointInDirectionBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::POINT_IN_DIRECTION_BRICK;
    $this->caption = "Point in direction "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::DEGREES_FORMULA] . " degrees";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}