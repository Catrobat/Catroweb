<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class AssertEqualsBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::ASSERT_EQUALS_BRICK;
    $this->caption = 'Assert Equals';
    $this->setImgFile(Constants::TESTING_BRICK_IMG);
  }
}
