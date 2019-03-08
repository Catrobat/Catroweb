<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class HideBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class HideBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::HIDE_BRICK;
    $this->caption = "Hide";

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}