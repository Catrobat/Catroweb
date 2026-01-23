<?php

declare(strict_types=1);

namespace App\Api\Services\Reactions;

use App\Api\Services\Base\AbstractRequestValidator;

class ReactionsRequestValidator extends AbstractRequestValidator
{
  // Validation is handled by the OpenAPI generated controller via @Assert\Choice constraint
  // This class is here to satisfy the ApiFacadeInterface contract
}
