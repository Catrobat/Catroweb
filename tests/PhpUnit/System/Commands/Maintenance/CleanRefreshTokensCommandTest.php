<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\Maintenance;

use App\DB\Entity\RefreshToken;
use App\System\Commands\Maintenance\CleanRefreshTokensCommand;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(CleanRefreshTokensCommand::class)]
final class CleanRefreshTokensCommandTest extends KernelTestCase
{
  public function testDeletesOnlyExpiredTokens(): void
  {
    self::bootKernel();
    $em = self::getContainer()->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $em);
    $token = static function (string $valid) use ($em): int|string|null {
      $token = new RefreshToken();
      $token->setRefreshToken(bin2hex(random_bytes(16)));
      $token->setUsername('someone');
      $token->setValid(new \DateTime($valid));
      $em->persist($token);
      $em->flush();

      return $token->getId();
    };
    $expired = $token('-1 day');
    $valid = $token('+1 day');

    $command = new CleanRefreshTokensCommand($em, new NullLogger());
    $dry = new CommandTester($command);
    $dry->execute(['--dry-run' => true]);
    $this->assertStringContainsString('expired refresh tokens would be deleted', $dry->getDisplay());
    $this->assertNotNull($em->find(RefreshToken::class, $expired));

    new CommandTester($command)->execute([]);
    $em->clear();

    $this->assertNull($em->find(RefreshToken::class, $expired));
    $this->assertNotNull($em->find(RefreshToken::class, $valid));
  }
}
