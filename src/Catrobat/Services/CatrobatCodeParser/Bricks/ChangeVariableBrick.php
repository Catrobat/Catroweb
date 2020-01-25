<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ChangeVariableBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class ChangeVariableBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::CHANGE_VARIABLE_BRICK;
    $this->caption = "Change variable _ by _";
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}