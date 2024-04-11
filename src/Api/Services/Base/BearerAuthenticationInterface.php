<?php

declare(strict_types=1);

namespace App\Api\Services\Base;

interface BearerAuthenticationInterface
{
  public function setBearerAuth(?string $value): void;

  public function getAuthenticationToken(): string;
}
