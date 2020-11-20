<?php

namespace Tests\phpUnit\CatrowebPhpUnit;

use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * @coversNothing
 *
 * @inernal
 */
class CatrowebTestCase extends TestCase
{
  /**
   * @param $object
   * @param $methodName
   * @param $parameters
   *
   * @throws ReflectionException
   *
   * @return mixed
   */
  public function invokeMethod(&$object, $methodName, array $parameters = [])
  {
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);

    return $method->invokeArgs($object, $parameters);
  }
}
