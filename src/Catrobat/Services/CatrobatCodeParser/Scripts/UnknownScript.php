<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class UnknownScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::UNKNOWN_SCRIPT;
    $this->caption = 'Unknown Script';
    $this->setImgFile(Constants::UNKNOWN_SCRIPT_IMG);
  }
}
