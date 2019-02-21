<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class ChooseCameraBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class ChooseCameraBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::CHOOSE_CAMERA_BRICK;
    $this->caption = "Use camera " . $this->brick_xml_properties
        ->xpath('spinnerValues/string')[(int)$this->brick_xml_properties->spinnerSelectionID];

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}