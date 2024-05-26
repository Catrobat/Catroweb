<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;
use App\Project\CatrobatCode\Parser\Scripts\Script;

class WhenNfcBrick extends Script
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::WHEN_NFC_BRICK;
    $this->caption = 'When NFC';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
