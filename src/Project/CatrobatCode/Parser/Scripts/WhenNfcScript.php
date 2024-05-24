<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Constants;

class WhenNfcScript extends Script
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::WHEN_NFC_SCRIPT;
    $this->caption = 'When NFC';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
