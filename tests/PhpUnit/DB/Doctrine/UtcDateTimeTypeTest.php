<?php

declare(strict_types=1);

namespace Tests\PhpUnit\DB\Doctrine;

use App\DB\Doctrine\UtcDateTimeType;
use App\DB\Entity\User\User;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UtcDateTimeType::class)]
#[Group('unit')]
final class UtcDateTimeTypeTest extends TestCase
{
  public function testRepeatedDstHourKeepsItsInstantAcrossPersistenceAndDifferentDefaultTimezones(): void
  {
    $default_timezone = date_default_timezone_get();
    try {
      date_default_timezone_set('Europe/Vienna');
      $type = new UtcDateTimeType();
      $platform = new MariaDBPlatform();
      // Both instants display as 02:30 locally when Vienna leaves DST.
      $first = new \DateTime('2026-10-25T02:30:00+02:00');
      $second = new \DateTime('2026-10-25T02:30:00+01:00');
      $stored_first = $type->convertToDatabaseValue($first, $platform);
      $stored_second = $type->convertToDatabaseValue($second, $platform);
      $this->assertSame('2026-10-25 00:30:00', $stored_first);
      $this->assertSame('2026-10-25 01:30:00', $stored_second);
      $this->assertSame('+02:00', $first->format('P'), 'storage must not mutate the entity');

      foreach (['Europe/Vienna', 'America/New_York', 'UTC'] as $timezone) {
        date_default_timezone_set($timezone);
        $hydrated_first = $type->convertToPHPValue($stored_first, $platform);
        $hydrated_second = $type->convertToPHPValue($stored_second, $platform);
        $this->assertSame($first->getTimestamp(), $hydrated_first->getTimestamp());
        $this->assertSame($second->getTimestamp(), $hydrated_second->getTimestamp());
        $this->assertSame(3600, $hydrated_second->getTimestamp() - $hydrated_first->getTimestamp());
        $this->assertSame('UTC', $hydrated_second->getTimezone()->getName());
        // A token from the first 02:30 must remain older than a change in the second 02:30.
        $this->assertLessThan($hydrated_second->getTimestamp(), $first->getTimestamp());
      }
    } finally {
      date_default_timezone_set($default_timezone);
    }
  }

  public function testInvalidValues(): void
  {
    $type = new UtcDateTimeType();
    $platform = new MariaDBPlatform();
    $this->expectException(InvalidFormat::class);
    $type->convertToPHPValue('invalid', $platform);
  }

  public function testCredentialChangesUseUtcEvenWithALocalDefaultTimezone(): void
  {
    $default_timezone = date_default_timezone_get();
    try {
      date_default_timezone_set('Europe/Vienna');
      $user = new User();
      $user->setPassword('first');
      $user->setPassword('second');
      $this->assertSame('UTC', $user->getCredentialsChangedAt()->getTimezone()->getName());
    } finally {
      date_default_timezone_set($default_timezone);
    }
  }
}
