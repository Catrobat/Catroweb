<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class GoNStepsBackBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class GoNStepsBackBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::GO_N_STEPS_BACK_BRICK;
    $this->caption = "Go back "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::STEPS_FORMUlA] . " layer";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}