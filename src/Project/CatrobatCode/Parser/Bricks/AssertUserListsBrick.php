<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class AssertUserListsBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ASSERT_USER_LISTS_BRICK;
    $this->caption = 'Assert User Lists Brick';
    $this->setImgFile(Constants::TESTING_BRICK_IMG);
  }
}
