<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class VibrationBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class VibrationBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::VIBRATION_BRICK;
    $this->caption = "Vibrate for _ second(s)";
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}