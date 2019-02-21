<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class StartScript
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts
 */
class StartScript extends Script
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::START_SCRIPT;
    $this->caption = "When program started";

    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}