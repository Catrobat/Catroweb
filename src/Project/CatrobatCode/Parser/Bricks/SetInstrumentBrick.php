<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetInstrumentBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_INSTRUMENT_BRICK;
    $this->caption = 'Set Instrument Brick';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
