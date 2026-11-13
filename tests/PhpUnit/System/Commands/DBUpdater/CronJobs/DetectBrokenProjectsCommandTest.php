<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\DBUpdater\CronJobs;

use App\Project\CatrobatFile\ProjectFileRepository;
use App\System\Commands\DBUpdater\CronJobs\DetectBrokenProjectsCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(DetectBrokenProjectsCommand::class)]
class DetectBrokenProjectsCommandTest extends TestCase
{
  private string $extract_dir;

  #[\Override]
  protected function setUp(): void
  {
    $this->extract_dir = sys_get_temp_dir().'/detect_broken_'.uniqid().'/';
    $fs = new Filesystem();
    $fs->dumpFile($this->extract_dir.'extract-only/code.xml', '<program/>');
    $fs->dumpFile($this->extract_dir.'both/code.xml', '<program/>');
  }

  #[\Override]
  protected function tearDown(): void
  {
    (new Filesystem())->remove($this->extract_dir);
  }

  public function testOnlyProjectsWithoutZipAndWithoutExtractedCodeAreBroken(): void
  {
    $updates = $this->runCommand(
      ['zip-only', 'extract-only', 'both', 'nothing'],
      zips: ['zip-only', 'both'],
    );

    self::assertSame(['nothing'], $updates[':true']);
    self::assertSame(['zip-only', 'extract-only', 'both'], $updates[':false']);
  }

  /**
   * @param list<string> $ids
   * @param list<string> $zips ids that still have a .catrobat zip
   *
   * @return array<string, list<string>> new flag value (':true'/':false') => ids passed to the update
   */
  private function runCommand(array $ids, array $zips): array
  {
    $pages = [array_map(static fn (string $id): array => ['id' => $id], $ids), []];
    $last_set = '';
    $updates = [];

    $query = $this->createStub(Query::class);
    $query->method('getArrayResult')->willReturnCallback(static function () use (&$pages): array {
      return array_shift($pages) ?? [];
    });

    $qb = $this->createStub(QueryBuilder::class);
    foreach (['select', 'from', 'orderBy', 'setFirstResult', 'setMaxResults', 'update', 'where', 'andWhere'] as $method) {
      $qb->method($method)->willReturnSelf();
    }
    $qb->method('set')->willReturnCallback(static function (string $field, string $value) use (&$last_set, $qb): QueryBuilder {
      $last_set = $value;

      return $qb;
    });
    $qb->method('setParameter')->willReturnCallback(static function (string $key, mixed $value) use (&$last_set, &$updates, $qb): QueryBuilder {
      if ('ids' === $key) {
        $updates[$last_set] = $value;
      }

      return $qb;
    });
    $qb->method('getQuery')->willReturn($query);

    $em = $this->createStub(EntityManagerInterface::class);
    $em->method('createQueryBuilder')->willReturn($qb);

    $file_repository = $this->createStub(ProjectFileRepository::class);
    $file_repository->method('checkIfProjectZipFileExists')->willReturnCallback(static fn (string $id): bool => in_array($id, $zips, true));

    $tester = new CommandTester(new DetectBrokenProjectsCommand($em, $file_repository, $this->extract_dir));
    $tester->execute([]);
    $tester->assertCommandIsSuccessful();

    return $updates;
  }
}
