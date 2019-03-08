<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class WhenGamepadButtonScript
 * @package App\Catrobat\Services\CatrobatCodeParser\Scripts
 */
class WhenGamepadButtonScript extends Script
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::WHEN_GAME_PAD_BUTTON_SCRIPT;
    $this->caption = "When gamepad button \"" . $this->script_xml_properties->action . "\" pressed";

    $this->setImgFile(Constants::CONTROL_SCRIPT_IMG);
  }
}