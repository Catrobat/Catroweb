<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\Scratch;

use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use App\Project\Scratch\Converter\ScratchConversionException;
use App\Project\Scratch\Converter\ScratchFetcher;
use App\Project\Scratch\Converter\ScratchToCatrobatConverter;
use App\Project\Scratch\ScratchManager;
use App\User\UserManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @internal
 */
#[CoversClass(ScratchManager::class)]
final class ScratchManagerTest extends TestCase
{
  #[DataProvider('converterVersions')]
  public function testACopyFromAnOlderConverterIsConvertedAgain(?int $version, bool $converts_again): void
  {
    $existing = new Project();
    $existing->setFilesize(1);
    $existing->setUser(new User());
    $existing->setLastModifiedAt(new \DateTime('2026-10-01'));
    $existing->setScratchConverterVersion($version);
    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('findOneByScratchId')->willReturn($existing);

    $fetcher = $this->createMock(ScratchFetcher::class);
    $fetcher->method('fetchMetadata')->willReturn(['history' => ['modified' => '2026-09-01T10:00:00.000Z'], 'author' => []]);
    // Stops the conversion right where it starts: whether it got there is all this test asks.
    $fetcher->expects($converts_again ? $this->once() : $this->never())->method('fetchProject')->willThrowException(new ScratchConversionException('stop'));

    $manager = new ScratchManager($project_manager, $this->createStub(UserManager::class), $fetcher,
      $this->createStub(ScratchToCatrobatConverter::class), new NullLogger(), $this->createStub(Connection::class),
      $this->createStub(EntityManagerInterface::class));

    try {
      $this->assertSame($existing, $manager->importProject(7, static function (): void {}, null));
    } catch (ScratchConversionException $exception) {
      $this->assertSame('stop', $exception->getMessage());
    }
  }

  /** @return iterable<string, array{?int, bool}> */
  public static function converterVersions(): iterable
  {
    yield 'made by the first converter, before versions were stored' => [null, true];
    yield 'made by an older converter' => [ScratchToCatrobatConverter::VERSION - 1, true];
    yield 'made by the current converter' => [ScratchToCatrobatConverter::VERSION, false];
  }
}
