<?php

namespace App\Utils;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\UuidGenerator;

/**
 * Class FixedUuidGenerator.
 */
class MyUuidGenerator extends UuidGenerator
{
  /**
   * @var string
   */
  private static $next_value = '';

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

  /**
   * @param $next_value
   */
  public static function setNextValue(string $next_value): void
  {
    MyUuidGenerator::$next_value = $next_value;
  }
}
