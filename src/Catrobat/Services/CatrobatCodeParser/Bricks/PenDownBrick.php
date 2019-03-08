<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class PenDownBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class PenDownBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::PEN_DOWN_BRICK;
    $this->caption = "Pen down";

    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}