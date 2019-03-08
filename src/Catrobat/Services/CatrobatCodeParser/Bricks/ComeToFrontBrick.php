<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ComeToFrontBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class ComeToFrontBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::COME_TO_FRONT_BRICK;
    $this->caption = "Go to front";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}