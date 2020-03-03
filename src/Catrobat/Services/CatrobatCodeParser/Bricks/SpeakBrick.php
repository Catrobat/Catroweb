<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SpeakBrick.
 */
class SpeakBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SPEAK_BRICK;
    $this->caption = 'Speak _';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
