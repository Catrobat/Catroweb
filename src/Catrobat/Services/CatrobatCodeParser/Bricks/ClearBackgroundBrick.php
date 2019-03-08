<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ClearBackgroundBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class ClearBackgroundBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::CLEAR_BACKGROUND_BRICK;
    $this->caption = "Clear";

    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}