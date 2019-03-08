<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class SetVolumeToBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class SetVolumeToBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::SET_VOLUME_TO_BRICK;
    $this->caption = "Set volume to "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::VOLUME_FORMUlA] . "%";

    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}