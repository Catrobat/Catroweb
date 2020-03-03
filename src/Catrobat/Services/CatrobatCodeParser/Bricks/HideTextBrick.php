<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class HideTextBrick.
 */
class HideTextBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::HIDE_TEXT_BRICK;
    $this->caption = 'Hide variable _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
