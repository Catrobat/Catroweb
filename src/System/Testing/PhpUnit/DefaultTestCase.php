<?php

namespace App\System\Testing\PhpUnit;

use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversNothing
 *
 * @internal
 */
class DefaultTestCase extends KernelTestCase
{
  /**
   * @throws ReflectionException
   *
   * @return mixed
   */
  public function invokeMethod(MockObject &$object, string $methodName, array $parameters = [])
  {
    $reflection = new ReflectionClass($object::class);
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
