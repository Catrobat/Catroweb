<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class AskSpeechBrick.
 */
class AskSpeechBrick extends Brick
{
  /**
   * @return mixed|void
   */
  protected function create()
  {
    $this->type = Constants::ASK_SPEECH_BRICK;
    $this->caption = 'Ask _ and store written answer in _';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
