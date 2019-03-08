<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class NoteBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class NoteBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::NOTE_BRICK;
    $this->caption = "Note "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::NOTE_FORMULA];

    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}