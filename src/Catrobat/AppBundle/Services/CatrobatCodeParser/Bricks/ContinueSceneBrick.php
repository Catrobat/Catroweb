<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class ContinueSceneBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::CONTINUE_SCENE_BRICK;
        $this->caption = "Continue scene " . $this->brick_xml_properties->sceneForTransition;

        $this->setImgFile(Constants::CONTROL_BRICK_IMG);
    }
}
