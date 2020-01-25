<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class PhiroPlayToneBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class PhiroPlayToneBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::PHIRO_PLAY_TONE_BRICK;
    $this->caption = "Play Phiro music tone";
    $this->setImgFile(Constants::PHIRO_SOUND_BRICK_IMG);
  }
}