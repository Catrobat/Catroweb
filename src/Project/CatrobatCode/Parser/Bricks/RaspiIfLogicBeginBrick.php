<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class RaspiIfLogicBeginBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::RASPI_IF_LOGIC_BEGIN_BRICK;
    $this->caption = 'If Raspberry Pi pin _ is true then';
    $this->setImgFile(Constants::RASPI_CONTROL_BRICK_IMG);
  }
}
