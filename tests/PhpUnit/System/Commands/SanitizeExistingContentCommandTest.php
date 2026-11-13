<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands;

use App\Moderation\TextSanitizer;
use App\System\Commands\SanitizeExistingContentCommand;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[CoversClass(SanitizeExistingContentCommand::class)]
class SanitizeExistingContentCommandTest extends KernelTestCase
{
  public function testReadsTheMappedTables(): void
  {
    self::bootKernel();
    $entity_manager = self::getContainer()->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $entity_manager);

    $queried = [];
    $connection = $this->createStub(Connection::class);
    $connection->method('fetchOne')->willReturnCallback(static function (string $sql) use (&$queried): int {
      $queried[] = $sql;

      return 0;
    });
    $batches = [];
    $connection->method('fetchAllAssociative')->willReturnCallback(static function (string $sql, array $params = []) use (&$batches): array {
      $batches[] = [$sql, $params];

      return [];
    });

    $sanitizer = new TextSanitizer($this->createStub(RequestStack::class), dirname(__DIR__, 4).'/config/moderation/wordlists');
    $tester = new CommandTester(new SanitizeExistingContentCommand($sanitizer, $connection, $entity_manager));
    $tester->execute(['--dry-run' => true]);

    $tester->assertCommandIsSuccessful();
    $this->assertSame([
      'SELECT COUNT(*) FROM program',
      'SELECT COUNT(*) FROM user_comment',
      'SELECT COUNT(*) FROM studio',
      'SELECT COUNT(*) FROM fos_user',
    ], $queried);

    // LIMIT must be a literal: DBAL binds positional params as strings, and MariaDB rejects LIMIT '1000'.
    foreach ($batches as [$sql, $params]) {
      $this->assertStringEndsWith('LIMIT 1000', $sql);
      $this->assertSame([], $params);
    }
    $this->assertCount(4, $batches);
  }
}
