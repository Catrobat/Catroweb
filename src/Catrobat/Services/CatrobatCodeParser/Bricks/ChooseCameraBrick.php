<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ChooseCameraBrick.
 */
class ChooseCameraBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::CHOOSE_CAMERA_BRICK;
    $this->caption = 'Use camera _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
