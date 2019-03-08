<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ForeverBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class ForeverBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::FOREVER_BRICK;
    $this->caption = "Forever";

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}