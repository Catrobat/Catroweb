<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\DBUpdater\CronJobs;

use App\DB\Entity\Project\Project;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\System\Commands\DBUpdater\CronJobs\DetectBrokenProjectsCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
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
    $fs->dumpFile($this->extract_dir.'restored/code.xml', '<program/>');
  }

  #[\Override]
  protected function tearDown(): void
  {
    (new Filesystem())->remove($this->extract_dir);
  }

  public function testOnlyProjectsWithoutZipAndWithoutExtractedCodeAreBroken(): void
  {
    [$updates, $reindexed] = $this->runCommand(
      ['zip-only' => false, 'extract-only' => false, 'both' => false, 'nothing' => false, 'restored' => true, 'still-lost' => true],
      zips: ['zip-only', 'both'],
    );

    self::assertSame(['nothing'], $updates[':true']);
    self::assertSame(['restored'], $updates[':false']);
    self::assertSame(['nothing', 'restored'], $reindexed);
  }

  /**
   * @param array<string, bool> $flags id => current has_missing_files
   * @param list<string>        $zips  ids that still have a .catrobat zip
   *
   * @return array{array<string, list<string>>, list<string>} new flag value (':true'/':false') => updated ids, and the re-indexed ids
   */
  private function runCommand(array $flags, array $zips): array
  {
    $rows = [];
    foreach ($flags as $id => $flag) {
      $rows[] = ['id' => (string) $id, 'has_missing_files' => $flag];
    }
    $pages = [$rows, []];
    $last_set = '';
    $updates = [];

    $query = $this->createStub(Query::class);
    $query->method('getArrayResult')->willReturnCallback(static function () use (&$pages): array {
      return [] === $pages ? [] : array_shift($pages);
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

    $repository = $this->createStub(EntityRepository::class);
    $repository->method('findBy')->willReturnCallback(function (array $criteria): array {
      return array_map(function (string $id): Project {
        $project = $this->createStub(Project::class);
        $project->method('getId')->willReturn($id);

        return $project;
      }, $criteria['id']);
    });

    $em = $this->createStub(EntityManagerInterface::class);
    $em->method('createQueryBuilder')->willReturn($qb);
    $em->method('getRepository')->willReturn($repository);

    $reindexed = [];
    $persister = $this->createStub(ObjectPersisterInterface::class);
    $persister->method('replaceMany')->willReturnCallback(static function (array $projects) use (&$reindexed): void {
      foreach ($projects as $project) {
        $reindexed[] = (string) $project->getId();
      }
    });

    $file_repository = $this->createStub(ProjectFileRepository::class);
    $file_repository->method('checkIfProjectZipFileExists')->willReturnCallback(static fn (string $id): bool => in_array($id, $zips, true));

    $tester = new CommandTester(new DetectBrokenProjectsCommand($em, $file_repository, $this->extract_dir, $persister));
    $tester->execute([]);
    $tester->assertCommandIsSuccessful();

    return [$updates, $reindexed];
  }
}
