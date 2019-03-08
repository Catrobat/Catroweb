<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\FormulaResolver;

/**
 * Class MoveNStepsBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class MoveNStepsBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::MOVE_N_STEPS_BRICK;
    $this->caption = "Move "
      . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::STEPS_FORMUlA] . " steps";

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}