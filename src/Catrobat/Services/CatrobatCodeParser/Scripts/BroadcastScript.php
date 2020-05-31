<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class BroadcastScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::BROADCAST_SCRIPT;
    $this->caption = 'When I receive _';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
