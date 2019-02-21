<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class ChangeYByNBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class ChangeYByNBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::CHANGE_Y_BY_N_BRICK;
    $this->caption = "Change Y by "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::Y_POSITION_CHANGE_FORMULA];

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}