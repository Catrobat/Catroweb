<?php
namespace Catrobat\CatrowebBundle\Services;

class TokenGenerator
{
  
  function __construct()
  {
  }
  
  function generateToken()
  {
    return uniqid(rand(),false);
  }
  
}
