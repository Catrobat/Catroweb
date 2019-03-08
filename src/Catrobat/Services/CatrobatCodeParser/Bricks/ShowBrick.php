<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ShowBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class ShowBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SHOW_BRICK;
    $this->caption = "Show";

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}