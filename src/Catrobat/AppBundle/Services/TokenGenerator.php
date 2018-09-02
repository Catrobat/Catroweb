<?php

namespace Catrobat\AppBundle\Services;

class TokenGenerator
{
  public function __construct()
  {
  }

  public function generateToken()
  {
    return md5(uniqid(rand(), false));
  }
}
