<?php

declare(strict_types=1);

namespace App\Api\Services\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractApiController extends AbstractController implements BearerAuthenticationInterface
{
  use BearerAuthenticationTrait;
}
