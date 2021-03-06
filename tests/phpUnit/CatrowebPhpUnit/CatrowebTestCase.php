<?php

namespace Tests\phpUnit\CatrowebPhpUnit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * @coversNothing
 *
 * @internal
 */
class CatrowebTestCase extends TestCase
{
  /**
   * @throws ReflectionException
   *
   * @return mixed
   */
  public function invokeMethod(MockObject &$object, string $methodName, array $parameters = [])
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
    $reflection = new \ReflectionClass($class);
    $property = $reflection->getProperty($property);
    $property->setAccessible(true);
    $property->setValue($instance, $value);
  }
}
