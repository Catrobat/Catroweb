<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class StampBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class StampBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::STAMP_BRICK;
    $this->caption = "Stamp";

    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}