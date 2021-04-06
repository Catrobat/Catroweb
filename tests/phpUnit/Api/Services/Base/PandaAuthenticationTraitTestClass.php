<?php

namespace Tests\phpUnit\Api\Services\Base;

use App\Api\Services\Base\PandaAuthenticationInterface;
use App\Api\Services\Base\PandaAuthenticationTrait;

class PandaAuthenticationTraitTestClass implements PandaAuthenticationInterface
{
  use PandaAuthenticationTrait;
}
