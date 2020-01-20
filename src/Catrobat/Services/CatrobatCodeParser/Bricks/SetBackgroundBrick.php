<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetBackgroundBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetBackgroundBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_BACKGROUND_BRICK;
    $this->caption = "Set background";
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}