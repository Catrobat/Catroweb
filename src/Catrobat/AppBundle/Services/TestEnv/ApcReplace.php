<?php

namespace Catrobat\AppBundle\Services\TestEnv;

/**
 * Class ApcReplace
 * @package Catrobat\AppBundle\Services\TestEnv
 */
final class ApcReplace
{
  /**
   * @var array
   */
  protected $store = [];

  /**
   * @var string
   */
  private static $APC_OBJECTS = "LdapTestDriverFixture";

  /**
   * Call this method to get singleton
   *
   * @return ApcReplace()
   */
  public static function Instance()
  {
    static $inst = null;
    if ($inst === null)
    {
      $inst = new ApcReplace();
    }

    return $inst;
  }


  /**
   * @param $key
   * @param $value
   *
   * @return bool
   */
  public function apc_store($key, $value)
  {
    $this->store[$key] = $value;

    return true;
  }

  /**
   * @param $key
   *
   * @return bool|mixed
   */
  public function apc_fetch($key)
  {
    if (!isset($this->store[$key]))
    {
      return false;
    }

    return $this->store[$key];
  }

  /**
   * @param $key
   *
   * @return bool
   */
  public function apc_delete($key)
  {
    unset($this->store[$key]);

    return true;
  }

  /**
   * Private ctor so nobody else can instantiate it
   *
   */
  private function __construct()
  {

  }
}