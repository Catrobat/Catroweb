<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class BroadcastScript
 * @package App\Catrobat\Services\CatrobatCodeParser\Scripts
 */
class BroadcastScript extends Script
{
  /**
   * @return mixed|void
   */
  protected function create()
  {
    $this->type = Constants::BROADCAST_SCRIPT;
    $this->caption = "When I receive _";
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}