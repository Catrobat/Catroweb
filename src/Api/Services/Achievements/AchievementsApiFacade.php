<?php

declare(strict_types=1);

namespace App\Api\Services\Achievements;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

class AchievementsApiFacade extends AbstractApiFacade
{
  public function __construct(
    AuthenticationManager $authentication_manager,
    private readonly AchievementsResponseManager $response_manager,
    private readonly AchievementsApiLoader $loader,
    private readonly AchievementsApiProcessor $processor,
    private readonly AchievementsRequestValidator $request_validator,
  ) {
    parent::__construct($authentication_manager);
  }

  #[\Override]
  public function getResponseManager(): AchievementsResponseManager
  {
    return $this->response_manager;
  }

  #[\Override]
  public function getLoader(): AchievementsApiLoader
  {
    return $this->loader;
  }

  #[\Override]
  public function getProcessor(): AchievementsApiProcessor
  {
    return $this->processor;
  }

  #[\Override]
  public function getRequestValidator(): AchievementsRequestValidator
  {
    return $this->request_validator;
  }
}
