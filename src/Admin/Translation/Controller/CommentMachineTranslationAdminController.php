<?php

namespace App\Admin\Translation\Controller;

class CommentMachineTranslationAdminController extends AbstractMachineTranslationAdminController
{
  public function __construct()
  {
    parent::__construct(self::TYPE_COMMENT);
  }
}
