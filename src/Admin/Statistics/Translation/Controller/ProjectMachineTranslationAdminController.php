<?php

declare(strict_types=1);

namespace App\Admin\Statistics\Translation\Controller;

class ProjectMachineTranslationAdminController extends AbstractMachineTranslationAdminController
{
  #[\Override]
  protected string $type = self::TYPE_PROJECT;
}
