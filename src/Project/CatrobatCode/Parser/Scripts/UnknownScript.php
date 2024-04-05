<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Scripts;

use App\Project\CatrobatCode\Parser\Constants;

class UnknownScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::UNKNOWN_SCRIPT;
    $this->caption = 'Unknown Script';
    $this->setImgFile(Constants::UNKNOWN_SCRIPT_IMG);
  }
}
