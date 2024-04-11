<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Base;

use App\Api\Services\Base\BearerAuthenticationInterface;
use App\Api\Services\Base\BearerAuthenticationTrait;

class BearerAuthenticationTraitTestClass implements BearerAuthenticationInterface
{
  use BearerAuthenticationTrait;
}
