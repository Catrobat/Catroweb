<?php

namespace App\DB\Generator;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Exception;
use Ramsey\Uuid\Doctrine\UuidGenerator;

/**
 * Class MyUuidGenerator.
 *
 * This Class can be used as Strategy for GuidType ID generation using Doctrine.
 * This allows to work with fixed UUID values in the test environment to ease the testing process.
 */
class MyUuidGenerator extends AbstractIdGenerator
{
  protected UuidGenerator $uuid_generator;

  public function __construct(UuidGenerator $uuid_generator)
  {
    $this->uuid_generator = $uuid_generator;
  }

  private static string $next_value = '';

  /**
   * @param mixed $entity
   *
   * @throws Exception
   *
   * @return string
   */
  public function generate(EntityManager $em, $entity)
  {
    $app_env = $_ENV['APP_ENV'];

    if ('test' === $app_env && '' !== MyUuidGenerator::$next_value) {
      $new_uuid = MyUuidGenerator::$next_value;
      MyUuidGenerator::$next_value = '';

      return $new_uuid;
    }

    return $this->uuid_generator->generate($em, $entity)->toString();
  }

  public static function setNextValue(string $next_value): void
  {
    MyUuidGenerator::$next_value = $next_value;
  }
}
