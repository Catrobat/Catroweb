<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class PenUpBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class PenUpBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::PEN_UP_BRICK;
    $this->caption = "Pen up";

    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}