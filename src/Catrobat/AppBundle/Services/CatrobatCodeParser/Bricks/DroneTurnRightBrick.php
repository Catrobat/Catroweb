<?php
namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class DroneTurnRightBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::AR_DRONE_TURN_RIGHT_BRICK;
        $formulas = FormulaResolver::resolve($this->brick_xml_properties->formulaList);

        $this->caption = "TURN the drone RIGHT for " . $formulas[Constants::DRONE_TIME_TO_FLY_IN_SECONDS]
            . " seconds with " . $formulas[Constants::AR_DRONE_POWER_IN_PERCENT] . "% power";

        $this->setImgFile(Constants::AR_DRONE_BRICK_IMG);
    }
}