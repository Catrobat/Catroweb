<?php

namespace App\Utils;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\UuidGenerator;

/**
 * Class MyUuidGenerator.
 *
 * This Class can be used as Strategy for GuidType ID generation using Doctrine.
 * This allows to work with fixed UUID values in the test environment to ease the testing process.
 */
class MyUuidGenerator extends UuidGenerator
{
  private static string $next_value = '';

  /**
   * {@inheritdoc}
   */
  public function generate(EntityManager $em, $entity)
  {
    $app_env = $_ENV['APP_ENV'];

    if ('test' === $app_env && '' !== MyUuidGenerator::$next_value)
    {
      $new_uuid = MyUuidGenerator::$next_value;
      MyUuidGenerator::$next_value = '';

      return $new_uuid;
    }

    return parent::generate($em, $entity);
  }

  public static function setNextValue(string $next_value): void
  {
    MyUuidGenerator::$next_value = $next_value;
  }
}
