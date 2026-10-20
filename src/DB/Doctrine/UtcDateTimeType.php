<?php

declare(strict_types=1);

namespace App\DB\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;

/** A DATETIME whose wall time always represents UTC, including during hydration. */
class UtcDateTimeType extends DateTimeType
{
  #[\Override]
  public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
  {
    if ($value instanceof \DateTime) {
      $value = (clone $value)->setTimezone(new \DateTimeZone('UTC'));
    }

    return parent::convertToDatabaseValue($value, $platform);
  }

  #[\Override]
  public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?\DateTime
  {
    if (null === $value) {
      return null;
    }
    if ($value instanceof \DateTime) {
      return (clone $value)->setTimezone(new \DateTimeZone('UTC'));
    }

    if (!\is_string($value)) {
      throw InvalidType::new($value, static::class, ['null', \DateTime::class, 'string']);
    }

    $date_time = \DateTime::createFromFormat('!'.$platform->getDateTimeFormatString(), $value, new \DateTimeZone('UTC'));
    if (false === $date_time) {
      throw InvalidFormat::new($value, static::class, $platform->getDateTimeFormatString());
    }

    return $date_time;
  }
}
