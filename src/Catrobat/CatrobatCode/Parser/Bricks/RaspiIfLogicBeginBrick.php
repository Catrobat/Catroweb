<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class RaspiIfLogicBeginBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::RASPI_IF_LOGIC_BEGIN_BRICK;
    $this->caption = 'If Raspberry Pi pin _ is true then';
    $this->setImgFile(Constants::RASPI_CONTROL_BRICK_IMG);
  }
}
