<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\TokenGenerator;

/**
 * Class ProxyTokenGenerator.
 */
class ProxyTokenGenerator extends TokenGenerator
{
  /**
   * @var TokenGenerator
   */
  private $generator;

  /**
   * ProxyTokenGenerator constructor.
   */
  public function __construct(TokenGenerator $default_generator)
  {
    parent::__construct();
    $this->generator = $default_generator;
  }

  /**
   * @return string
   */
  public function generateToken()
  {
    return $this->generator->generateToken();
  }

  public function setTokenGenerator(TokenGenerator $generator)
  {
    $this->generator = $generator;
  }
}
