<?php
namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

class JumpingSumoJumpHighBrick extends Brick
{
    protected function create()
    {
        $this->type = Constants::JUMP_SUMO_JUMP_HIGH_BRICK;

        $this->caption = "Make a high jump";

        $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
    }
}