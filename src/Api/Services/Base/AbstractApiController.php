<?php

namespace App\Api\Services\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractApiController extends AbstractController implements PandaAuthenticationInterface
{
  use PandaAuthenticationTrait;
}
