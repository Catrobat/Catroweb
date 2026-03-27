<?php

declare(strict_types=1);

namespace App\Admin\Statistics\Translation\Controller;

class CommentMachineTranslationAdminController extends AbstractMachineTranslationAdminController
{
  #[\Override]
  protected string $type = self::TYPE_COMMENT;
}
