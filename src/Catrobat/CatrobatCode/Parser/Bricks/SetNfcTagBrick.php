<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetNfcTagBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_NFC_TAG_BRICK;
    $this->caption = 'Wait for next NFC tag to write';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
