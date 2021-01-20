<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class AssertUserListsBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ASSERT_USER_LISTS_BRICK;
    $this->caption = 'Assert User Lists Brick';
    $this->setImgFile(Constants::TESTING_BRICK_IMG);
  }
}
