<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class SetRotationStyleBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class SetRotationStyleBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_ROTATION_STYLE_BRICK;

    $rotation_style = null;
    switch ((string)$this->brick_xml_properties->selection)
    {
      case 0:
        $rotation_style = 'left-right only';
        break;
      case 1:
        $rotation_style = 'all-around';
        break;
      case 2:
        $rotation_style = "don't rotate";
        break;
      default:
        $rotation_style = "unknown";
        break;
    }
    $this->caption = "Set rotation style " . $rotation_style;

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}