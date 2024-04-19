<?php

declare(strict_types=1);

namespace App\System\Testing\PhpUnit;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversNothing
 *
 * @internal
 */
class DefaultTestCase extends KernelTestCase
{
  /**
   * @throws \ReflectionException
   */
  public function invokeMethod(MockObject $object, string $methodName, array $parameters = []): mixed
  {
    $reflection = new \ReflectionClass($object::class);
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);

    return $method->invokeArgs($object, $parameters);
  }

  /**
   * @throws \ReflectionException
   */
  public static function mockProperty(mixed $class, mixed $instance, mixed $property, mixed $value): void
  {
    $reflection = new \ReflectionClass($class);
    $property = $reflection->getProperty($property);
    $property->setAccessible(true);
    $property->setValue($instance, $value);
  }
}
