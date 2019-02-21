<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class CameraBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class CameraBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::CAMERA_BRICK;
    $this->caption = "Turn camera " . $this->brick_xml_properties
        ->xpath('spinnerValues/string')[(int)$this->brick_xml_properties->spinnerSelectionID];

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}