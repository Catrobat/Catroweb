<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ChangeTransparencyByNBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CHANGE_TRANSPARENCY_BY_N_BRICK;
    $this->caption = 'Change transparency by _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
