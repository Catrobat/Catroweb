<?php

namespace App\Admin\Translation\Controller;

class CommentMachineTranslationAdminController extends MachineTranslationAdminController
{
  public function __construct()
  {
    parent::__construct(self::TYPE_COMMENT);
  }
}
