<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\Maintenance;

use App\DB\Entity\RefreshToken;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\System\Commands\Maintenance\CleanStaleRecordsCommand;
use App\System\Testing\DataFixtures\UserDataFixtures;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(CleanStaleRecordsCommand::class)]
final class CleanStaleRecordsCommandTest extends KernelTestCase
{
  private EntityManagerInterface $em;

  private User $user;

  #[\Override]
  protected function setUp(): void
  {
    self::bootKernel();
    $em = self::getContainer()->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $em);
    $this->em = $em;
    $users = self::getContainer()->get(UserDataFixtures::class);
    $this->assertInstanceOf(UserDataFixtures::class, $users);
    $this->user = $users->insertUser(['name' => 'stale'.uniqid()]);
  }

  public function testDeletesExpiredTokensAndOldSoftDeletedCommentsWithoutLiveReplies(): void
  {
    $expired = $this->token('-1 day');
    $valid = $this->token('+1 day');
    $purged = $this->comment(true, '-40 days');
    $recent = $this->comment(true, '-10 days');
    $live = $this->comment(false, '-40 days');
    $parent_of_live_reply = $this->comment(true, '-40 days');
    $this->comment(false, '-35 days', $parent_of_live_reply);

    $command = new CleanStaleRecordsCommand($this->em, new NullLogger());
    $dry = new CommandTester($command);
    $dry->execute(['--dry-run' => true]);
    $this->assertStringContainsString('would be removed', $dry->getDisplay());
    $this->assertNotNull($this->em->find(RefreshToken::class, $expired));

    new CommandTester($command)->execute([]);
    $this->em->clear();

    $this->assertNull($this->em->find(RefreshToken::class, $expired));
    $this->assertNotNull($this->em->find(RefreshToken::class, $valid));
    $this->assertNull($this->em->find(UserComment::class, $purged));
    $this->assertNotNull($this->em->find(UserComment::class, $recent));
    $this->assertNotNull($this->em->find(UserComment::class, $live));
    $this->assertNotNull($this->em->find(UserComment::class, $parent_of_live_reply));
  }

  private function token(string $valid): int|string|null
  {
    $token = new RefreshToken();
    $token->setRefreshToken(bin2hex(random_bytes(16)));
    $token->setUsername($this->user->getUserIdentifier());
    $token->setValid(new \DateTime($valid));
    $this->em->persist($token);
    $this->em->flush();

    return $token->getId();
  }

  private function comment(bool $deleted, string $age, ?string $parent_id = null): string
  {
    $comment = new UserComment()
      ->setUser($this->user)
      ->setUsername($this->user->getUserIdentifier())
      ->setText('stale test')
      ->setUploadDate(new \DateTime($age))
      ->setIsDeleted($deleted)
      ->setParentId($parent_id)
    ;
    $this->em->persist($comment);
    $this->em->flush();

    return (string) $comment->getId();
  }
}
