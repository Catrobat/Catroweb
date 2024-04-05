<?php

declare(strict_types=1);

namespace App\Api\Services\Base;

interface ApiFacadeInterface
{
  public function getResponseManager(): AbstractResponseManager;

  public function getLoader(): AbstractApiLoader;

  public function getProcessor(): AbstractApiProcessor;

  public function getRequestValidator(): AbstractRequestValidator;
}
