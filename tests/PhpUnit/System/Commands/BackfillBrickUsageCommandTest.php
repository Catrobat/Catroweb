<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\ProjectCodeStatistics;
use App\Project\CodeStatistics\ProjectBrickUsageWriter;
use App\System\Commands\BackfillBrickUsageCommand;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(BackfillBrickUsageCommand::class)]
#[CoversClass(ProjectBrickUsageWriter::class)]
final class BackfillBrickUsageCommandTest extends KernelTestCase
{
  private const string CODE_XML = '<program><objectList><object name="a"><scriptList><script type="StartScript"><brickList>'
    .'<brick type="WaitBrick"/><brick type="WaitBrick"/><brick type="SetYBrick"/>'
    .'</brickList></script></scriptList></object></objectList></program>';

  private EntityManagerInterface $entity_manager;

  private string $owner;

  #[\Override]
  protected function setUp(): void
  {
    self::bootKernel();
    $entity_manager = self::getContainer()->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $entity_manager);
    $this->entity_manager = $entity_manager;
    $users = self::getContainer()->get(UserDataFixtures::class);
    $this->assertInstanceOf(UserDataFixtures::class, $users);
    $this->owner = 'bricks'.uniqid();
    $users->insertUser(['name' => $this->owner]);
  }

  public function testPersistingStatisticsReplacesTheUsageRows(): void
  {
    $project = $this->fixtures()->insertProject(['name' => 'usage '.uniqid(), 'owned by' => $this->owner]);
    $id = (string) $project->getId();

    $this->persistStatistics($id, ['SetXBrick' => 2, 'WaitBrick' => 1]);
    $this->assertSame(['SetXBrick' => 2, 'WaitBrick' => 1], $this->usage($id));

    $this->persistStatistics($id, ['SetXBrick' => 5]);
    $this->assertSame(['SetXBrick' => 5], $this->usage($id));
  }

  public function testStoresOnlyClassNameTypesAndKeepsCaseApart(): void
  {
    $id = (string) $this->fixtures()->insertProject(['name' => 'types '.uniqid(), 'owned by' => $this->owner])->getId();

    $this->persistStatistics($id, ['WaitBrick' => 1, 'waitbrick' => 1, 'WaitBrick ' => 1, '<x>' => 1]);

    $this->assertSame(['WaitBrick' => 1, 'waitbrick' => 1], $this->usage($id));
    $this->assertSame(1, (int) $this->entity_manager->getConnection()->fetchOne(
      'SELECT COUNT(*) FROM project_code_statistics WHERE program_id = ?', [$id],
    ));
  }

  public function testFillsUsageFromStatisticsAndStoredFilesAndIsSafeToRerun(): void
  {
    $fixtures = $this->fixtures();
    $with_stats = (string) $fixtures->insertProject(['name' => 'stats '.uniqid(), 'owned by' => $this->owner])->getId();
    $with_file = (string) $fixtures->insertProject(['name' => 'file '.uniqid(), 'owned by' => $this->owner])->getId();
    $without_file = (string) $fixtures->insertProject(['name' => 'none '.uniqid(), 'owned by' => $this->owner])->getId();

    // Statistics from before the usage table existed
    $this->persistStatistics($with_stats, ['SetXBrick' => 3]);
    $this->entity_manager->getConnection()->executeStatement('DELETE FROM project_brick_usage WHERE project_id = ?', [$with_stats]);

    $storage_dir = self::getContainer()->getParameter('catrobat.file.storage.dir');
    $this->assertIsString($storage_dir);
    $zip_path = $storage_dir.$with_file.'.catrobat';
    $zip = new \ZipArchive();
    $zip->open($zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
    $zip->addFromString('nested/code.xml', self::CODE_XML);
    $zip->close();

    try {
      $tester = $this->runBackfill();
      $this->assertStringContainsString('without a readable code.xml', $tester->getDisplay());

      $this->assertSame(['SetXBrick' => 3], $this->usage($with_stats));
      $this->assertSame(['SetYBrick' => 1, 'WaitBrick' => 2], $this->usage($with_file));
      $this->assertSame([], $this->usage($without_file));
      $this->assertSame(1, (int) $this->entity_manager->getConnection()->fetchOne(
        'SELECT COUNT(*) FROM project_code_statistics WHERE program_id = ?', [$with_file],
      ));

      // A rerun skips what is done and parses nothing twice
      $this->runBackfill();
      $this->assertSame(['SetYBrick' => 1, 'WaitBrick' => 2], $this->usage($with_file));
      $this->assertSame(1, (int) $this->entity_manager->getConnection()->fetchOne(
        'SELECT COUNT(*) FROM project_code_statistics WHERE program_id = ?', [$with_file],
      ));
    } finally {
      unlink($zip_path);
    }
  }

  private function runBackfill(): CommandTester
  {
    $kernel = self::$kernel;
    $this->assertNotNull($kernel);
    $tester = new CommandTester(new Application($kernel)->find('catro:backfill:brick-usage'));
    $this->assertSame(0, $tester->execute(['--batch-size' => '2']));

    return $tester;
  }

  private function fixtures(): ProjectDataFixtures
  {
    $fixtures = self::getContainer()->get(ProjectDataFixtures::class);
    $this->assertInstanceOf(ProjectDataFixtures::class, $fixtures);

    return $fixtures;
  }

  /**
   * @param array<string, int> $brick_counts
   */
  private function persistStatistics(string $project_id, array $brick_counts): void
  {
    $project = $this->entity_manager->find(Project::class, $project_id);
    $this->assertNotNull($project);
    $stats = new ProjectCodeStatistics()->setBrickCounts($brick_counts);
    $stats->setProject($project);
    $this->entity_manager->persist($stats);
    $this->entity_manager->flush();
  }

  /**
   * @return array<string, int>
   */
  private function usage(string $project_id): array
  {
    $rows = $this->entity_manager->getConnection()->fetchAllKeyValue(
      'SELECT brick_type, count FROM project_brick_usage WHERE project_id = ? ORDER BY brick_type', [$project_id],
    );

    return array_map(intval(...), $rows);
  }
}
