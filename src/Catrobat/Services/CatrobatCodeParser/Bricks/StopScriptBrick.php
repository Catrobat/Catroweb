<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class StopScriptBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class StopScriptBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::STOP_SCRIPT_BRICK;
    $this->caption =
      $this->brick_xml_properties->xpath('spinnerValue/string')[(int)$this->brick_xml_properties->spinnerSelection];

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}