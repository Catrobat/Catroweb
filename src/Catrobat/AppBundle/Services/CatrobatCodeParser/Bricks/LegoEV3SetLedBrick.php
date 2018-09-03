<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class LegoEV3SetLedBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::LEGO_EV3_SET_LED_BRICK;
    $this->caption = "Set EV3 LED Status " . $this->brick_xml_properties->ledStatus;

    $this->setImgFile(Constants::LEGO_EV3_BRICK_IMG);
  }
}