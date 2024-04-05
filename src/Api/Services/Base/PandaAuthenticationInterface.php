<?php

declare(strict_types=1);

namespace App\Api\Services\Base;

interface PandaAuthenticationInterface
{
  public function setPandaAuth(?string $value): void;

  public function getAuthenticationToken(): string;
}
