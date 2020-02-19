<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class UserVariableBrick.
 */
class UserVariableBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::USER_VARIABLE_BRICK;
    $this->caption = 'User Variable';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
