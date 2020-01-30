<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class IfBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class IfBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::IF_BRICK;
    $this->caption = "If _ is true then";
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}