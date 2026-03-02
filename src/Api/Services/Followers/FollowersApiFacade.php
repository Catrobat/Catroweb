<?php

declare(strict_types=1);

namespace App\Api\Services\Followers;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;

class FollowersApiFacade extends AbstractApiFacade
{
  public function __construct(
    AuthenticationManager $authentication_manager,
    private readonly FollowersResponseManager $response_manager,
    private readonly FollowersApiLoader $loader,
    private readonly FollowersApiProcessor $processor,
    private readonly FollowersRequestValidator $request_validator,
  ) {
    parent::__construct($authentication_manager);
  }

  #[\Override]
  public function getResponseManager(): FollowersResponseManager
  {
    return $this->response_manager;
  }

  #[\Override]
  public function getLoader(): FollowersApiLoader
  {
    return $this->loader;
  }

  #[\Override]
  public function getProcessor(): FollowersApiProcessor
  {
    return $this->processor;
  }

  #[\Override]
  public function getRequestValidator(): FollowersRequestValidator
  {
    return $this->request_validator;
  }
}
