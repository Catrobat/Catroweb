<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class ChangeSizeByNBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class ChangeSizeByNBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::CHANGE_SIZE_BY_N_BRICK;
    $this->caption = "Change size by "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::SIZE_CHANGE_FORMULA];

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}