<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class PrevLookBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class PrevLookBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::PREV_LOOK_BRICK;
    $this->caption = "Previous look";
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}