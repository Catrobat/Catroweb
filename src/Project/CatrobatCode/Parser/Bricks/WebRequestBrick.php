<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class WebRequestBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::WEB_REQUEST_BRICK;
    $this->caption = 'Web Request';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
