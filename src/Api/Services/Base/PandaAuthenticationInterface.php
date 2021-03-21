<?php

namespace App\Api\Services\Base;

interface PandaAuthenticationInterface
{
  public function setPandaAuth($value);

  public function getAuthenticationToken();
}
