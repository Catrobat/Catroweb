<?php
namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class JumpingSumoJumpLongBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::JUMP_SUMO_JUMP_LONG_BRICK;

        $this->caption = "Make a long jump";

        $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
    }
}