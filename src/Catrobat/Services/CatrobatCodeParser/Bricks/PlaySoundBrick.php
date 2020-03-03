<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class PlaySoundBrick.
 */
class PlaySoundBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::PLAY_SOUND_BRICK;
    $this->caption = 'Start sound';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
