<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class StopAllSoundsBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class StopAllSoundsBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::STOP_ALL_SOUNDS_BRICK;
    $this->caption = "Stop all sounds";

    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}