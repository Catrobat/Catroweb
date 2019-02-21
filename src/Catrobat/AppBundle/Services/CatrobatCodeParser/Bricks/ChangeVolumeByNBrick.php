<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class ChangeVolumeByNBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
 */
class ChangeVolumeByNBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::CHANGE_VOLUME_BY_N_BRICK;
    $this->caption = "Change volume by "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::VOLUME_CHANGE_FORMULA];

    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}