<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;
use App\Project\CatrobatCode\Parser\Scripts\Script;

class WhenRaspiPinChangedBrick extends Script
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::WHEN_RASPI_PIN_CHANGED_BRICK;
    $this->caption = 'When Raspberry pin _ changes to _';
    $this->setImgFile(Constants::RASPI_EVENT_SCRIPT_IMG);
  }
}
