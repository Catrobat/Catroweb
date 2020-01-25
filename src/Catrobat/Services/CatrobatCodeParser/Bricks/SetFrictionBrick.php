<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetFrictionBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetFrictionBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_FRICTION_BRICK;
    $this->caption = "Set friction to _ %";
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}