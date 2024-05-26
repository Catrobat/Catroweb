<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetListeningLanguageBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_LISTENING_LANGUAGE_BRICK;
    $this->caption = 'Set listening language to _';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
