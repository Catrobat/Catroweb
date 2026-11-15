<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\Maintenance;

use App\DB\Entity\User\Notifications\CatroNotification;
use App\System\Commands\Maintenance\CleanNotificationsCommand;
use App\System\Testing\DataFixtures\UserDataFixtures;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(CleanNotificationsCommand::class)]
final class CleanNotificationsCommandTest extends KernelTestCase
{
  public function testDeletesReadAfter90DaysAndUnreadAfterAYear(): void
  {
    self::bootKernel();
    $em = self::getContainer()->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $em);
    $users = self::getContainer()->get(UserDataFixtures::class);
    $this->assertInstanceOf(UserDataFixtures::class, $users);
    $user = $users->insertUser(['name' => 'notif'.uniqid()]);

    // [seen, age, kept]
    $cases = [[true, '-100 days', false], [true, '-30 days', true], [false, '-100 days', true], [false, '-400 days', false]];
    $ids = [];
    foreach ($cases as [$seen, $age]) {
      $notification = new CatroNotification($user);
      $notification->setSeen($seen);
      $notification->setCreatedAt(new \DateTime($age));
      $em->persist($notification);
      $em->flush();
      $ids[] = $notification->getId();
    }

    $command = new CleanNotificationsCommand($em, new NullLogger());
    $dry = new CommandTester($command);
    $dry->execute(['--dry-run' => true]);
    $this->assertStringContainsString('notifications would be deleted', $dry->getDisplay());

    new CommandTester($command)->execute([]);
    $em->clear();

    foreach ($cases as $i => [$seen, $age, $kept]) {
      $this->assertSame($kept, null !== $em->find(CatroNotification::class, $ids[$i]), "seen={$seen} age={$age}");
    }
  }
}
