<?php

namespace Tests\phpUnit\TestUtils;

use ReflectionClass;
use ReflectionException;

/**
 * Class PHPUnitUtils.
 */
class PHPUnitUtils
{
  /**
   * Call protected/private method of a class.
   *
   * @param object $object     instantiated object that we will run method on
   * @param string $methodName Method name to call
   * @param array  $parameters array of parameters to pass into method
   *
   * @throws ReflectionException
   *
   * @return mixed method return
   */
  public static function invokeMethod(object $object, string $methodName, array $parameters = [])
  {
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);

    return $method->invokeArgs($object, $parameters);
  }

  /**
   * @param mixed $class
   * @param mixed $instance
   * @param mixed $property
   * @param mixed $value
   *
   * @throws ReflectionException
   */
  public static function mockProperty($class, $instance, $property, $value): void
  {
    $reflection = new ReflectionClass($class);
    $property = $reflection->getProperty($property);
    $property->setAccessible(true);
    $property->setValue($instance, $value);
  }
}
