<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class PlaySoundWaitBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class PlaySoundWaitBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::PLAY_SOUND_WAIT_BRICK;
    $this->caption = "Start sound and wait";
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }

}