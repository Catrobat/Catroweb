<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SpeakWaitBrick.
 */
class SpeakWaitBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SPEAK_WAIT_BRICK;
    $this->caption = 'Speak _ and wait';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
