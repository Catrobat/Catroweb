<?php

namespace App\Api\Services\Base;

interface PandaAuthenticationInterface
{
  public function setPandaAuth(?string $value): void;

  public function getAuthenticationToken(): string;
}
