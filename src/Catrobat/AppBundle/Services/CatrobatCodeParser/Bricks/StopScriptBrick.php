<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class StopScriptBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::STOP_SCRIPT_BRICK;
    $this->caption =
      $this->brick_xml_properties->xpath('spinnerValue/string')[(int)$this->brick_xml_properties->spinnerSelection];

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}