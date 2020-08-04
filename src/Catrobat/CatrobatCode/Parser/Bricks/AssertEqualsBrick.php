<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class AssertEqualsBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ASSERT_EQUALS_BRICK;
    $this->caption = 'Assert Equals';
    $this->setImgFile(Constants::TESTING_BRICK_IMG);
  }
}
