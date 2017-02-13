<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;
use Catrobat\AppBundle\Services\CatrobatCodeParser\FormulaResolver;

class SetVolumeToBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::SET_VOLUME_TO_BRICK;
        $this->caption = "Set volume to "
          . FormulaResolver::resolve($this->brick_xml_properties->formulaList)[Constants::VOLUME_FORMUlA] . "%";

        $this->setImgFile(Constants::SOUND_BRICK_IMG);
    }
}