<?php

namespace Tests\PhpUnit\Api\Services\Base;

use App\Api\Services\Base\PandaAuthenticationInterface;
use App\Api\Services\Base\PandaAuthenticationTrait;

class PandaAuthenticationTraitTestClass implements PandaAuthenticationInterface
{
  use PandaAuthenticationTrait;
}
