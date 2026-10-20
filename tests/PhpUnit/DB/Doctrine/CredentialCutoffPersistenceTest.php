<?php

declare(strict_types=1);

namespace Tests\PhpUnit\DB\Doctrine;

use App\DB\Doctrine\UtcDateTimeType;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(UtcDateTimeType::class)]
final class CredentialCutoffPersistenceTest extends KernelTestCase
{
  public function testDoctrinePersistsAndHydratesBothOccurrencesOfTheRepeatedDstHour(): void
  {
    self::bootKernel();
    $entity_manager = self::getContainer()->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $entity_manager);
    $default_timezone = date_default_timezone_get();
    try {
      date_default_timezone_set('Europe/Vienna');
      foreach (['2026-10-25T02:30:00+02:00', '2026-10-25T02:30:00+01:00'] as $index => $instant) {
        $user = new User();
        $username = 'utc-cutoff-7422-'.$index;
        $user->setUsername($username);
        $user->setEmail($username.'@localhost.org');
        $user->setPassword('hash');
        $cutoff = new \DateTime($instant);
        new \ReflectionProperty(User::class, 'credentials_changed_at')->setValue($user, $cutoff);
        $entity_manager->persist($user);
        $entity_manager->flush();
        $id = $user->getId();
        $stored = $entity_manager->getConnection()->fetchOne('SELECT credentials_changed_at FROM fos_user WHERE id = ?', [$id]);
        $this->assertSame(0 === $index ? '2026-10-25 00:30:00' : '2026-10-25 01:30:00', $stored);
        $entity_manager->clear();
        $hydrated = $entity_manager->find(User::class, $id);
        $this->assertInstanceOf(User::class, $hydrated);
        $hydrated_cutoff = $hydrated->getCredentialsChangedAt();
        $this->assertNotNull($hydrated_cutoff);
        $this->assertSame($cutoff->getTimestamp(), $hydrated_cutoff->getTimestamp());
        $this->assertSame('UTC', $hydrated_cutoff->getTimezone()->getName());
        $entity_manager->remove($hydrated);
        $entity_manager->flush();
      }
    } finally {
      date_default_timezone_set($default_timezone);
    }
  }
}
