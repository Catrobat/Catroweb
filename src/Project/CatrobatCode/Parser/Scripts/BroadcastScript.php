<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Constants;

class BroadcastScript extends Script
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::BROADCAST_SCRIPT;
    $this->caption = 'When I receive _';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
