<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ElseBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class ElseBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::ELSE_BRICK;
    $this->caption = "Else";

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}