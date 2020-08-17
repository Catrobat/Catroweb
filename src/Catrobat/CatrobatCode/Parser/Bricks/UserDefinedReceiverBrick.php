<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class UserDefinedReceiverBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::USER_DEFINED_RECEIVER_BRICK;
    $this->caption = 'User Defined Receiver Brick';
    $this->setImgFile(Constants::YOUR_BRICK_IMG);
  }
}
