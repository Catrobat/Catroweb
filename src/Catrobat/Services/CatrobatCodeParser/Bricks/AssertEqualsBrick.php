<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class AssertEqualsBrick.
 */
class AssertEqualsBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::ASSERT_EQUALS_BRICK;
    $this->caption = 'Assert Equals';
    $this->setImgFile(Constants::TESTING_BRICK_IMG);
  }
}
