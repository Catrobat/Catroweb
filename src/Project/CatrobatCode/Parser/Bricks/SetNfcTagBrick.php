<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetNfcTagBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_NFC_TAG_BRICK;
    $this->caption = 'Wait for next NFC tag to write';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
