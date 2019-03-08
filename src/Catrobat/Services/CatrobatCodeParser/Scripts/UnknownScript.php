<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class UnknownScript
 * @package App\Catrobat\Services\CatrobatCodeParser\Scripts
 */
class UnknownScript extends Script
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::UNKNOWN_SCRIPT;
    $this->caption = "Unknown Script";

    $this->setImgFile(Constants::UNKNOWN_SCRIPT_IMG);
  }
}