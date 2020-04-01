<?php

namespace App\Catrobat\Services\TestEnv;

final class ApcReplace
{
  private array $store = [];

  private static string $APC_OBJECTS = 'LdapTestDriverFixture';

  /**
   * Private ctor so nobody else can instantiate it.
   */
  private function __construct()
  {
  }

  /**
   * Call this method to get singleton.
   *
   * @return ApcReplace()
   */
  public static function Instance()
  {
    static $inst = null;
    if (null === $inst)
    {
      $inst = new ApcReplace();
    }

    return $inst;
  }

  /**
   * @param mixed $key
   * @param mixed $value
   */
  public function apc_store($key, $value): bool
  {
    $this->store[$key] = $value;

    return true;
  }

  /**
   * @param mixed $key
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
   * @param mixed $key
   */
  public function apc_delete($key): bool
  {
    unset($this->store[$key]);

    return true;
  }
}
