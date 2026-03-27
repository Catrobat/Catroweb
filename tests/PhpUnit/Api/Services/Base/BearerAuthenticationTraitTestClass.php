<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Base;

use App\Api\Services\Base\BearerAuthenticationInterface;
use App\Api\Services\Base\BearerAuthenticationTrait;
use Symfony\Component\HttpFoundation\Request;

class BearerAuthenticationTraitTestClass implements BearerAuthenticationInterface
{
  use BearerAuthenticationTrait;

  private ?Request $request = null;

  public function setCurrentRequest(?Request $request): void
  {
    $this->request = $request;
  }

  protected function getCurrentRequest(): ?Request
  {
    return $this->request;
  }
}
