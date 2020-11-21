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
}
