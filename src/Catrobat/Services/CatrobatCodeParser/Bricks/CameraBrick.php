<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class CameraBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
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