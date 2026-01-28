<?php

declare(strict_types=1);

namespace App\DB\Entity\MediaLibrary;

enum MediaFileType: string
{
  case IMAGE = 'IMAGE';
  case SOUND = 'SOUND';
}
