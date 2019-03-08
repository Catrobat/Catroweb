<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class ChangeTransparencyByNBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class ChangeTransparencyByNBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::CHANGE_TRANSPARENCY_BY_N_BRICK;
    $this->caption = "Change transparency by "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::TRANSPARENCY_CHANGE_FORMULA];

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}