<?php


namespace Tests\phpUnit\CatrowebPhpUnit;

use PHPUnit\Framework\TestCase;
use ReflectionException;


class CatrowebTestCase extends TestCase
{
  /**
   * @param $object
   * @param $methodName
   * @param array $parameters
   * @return mixed
   * @throws ReflectionException
   */
  public function invokeMethod(&$object, $methodName, array $parameters = array())
  {
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);
    return $method->invokeArgs($object, $parameters);
  }
}
