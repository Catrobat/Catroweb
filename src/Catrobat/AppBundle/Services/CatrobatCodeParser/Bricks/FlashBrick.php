<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class FlashBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class FlashBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::FLASH_BRICK;
    $this->caption = "Turn flashlight "
      . $this->brick_xml_properties->xpath('spinnerValues/string')[(int)$this->brick_xml_properties->spinnerSelectionID];

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}