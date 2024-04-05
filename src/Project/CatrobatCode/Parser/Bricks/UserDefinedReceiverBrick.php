<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class UserDefinedReceiverBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::USER_DEFINED_RECEIVER_BRICK;
    $this->caption = 'User Defined Receiver Brick';
    $this->setImgFile(Constants::YOUR_BRICK_IMG);
  }
}
