<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class DroneTakeOffLandBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class DroneTakeOffLandBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::AR_DRONE_TAKE_OFF_LAND_BRICK;
    $this->caption = "Take off or land drone";

    $this->setImgFile(Constants::AR_DRONE_BRICK_IMG);
  }
}