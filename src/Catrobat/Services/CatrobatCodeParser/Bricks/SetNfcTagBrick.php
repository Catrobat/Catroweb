<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class SetNfcTagBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_NFC_TAG_BRICK;
    $this->caption = 'Wait for next NFC tag to write';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
