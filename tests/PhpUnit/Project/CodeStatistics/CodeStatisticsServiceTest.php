<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CodeStatistics;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProjectCodeStatistics;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CodeStatistics\CodeStatisticsParser;
use App\Project\CodeStatistics\CodeStatisticsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
#[CoversClass(CodeStatisticsService::class)]
class CodeStatisticsServiceTest extends TestCase
{
  private string $temp_dir;

  #[\Override]
  protected function setUp(): void
  {
    $this->temp_dir = sys_get_temp_dir().'/code-statistics-service-'.uniqid('', true).'/';
    mkdir($this->temp_dir, 0777, true);
  }

  #[\Override]
  protected function tearDown(): void
  {
    if (is_file($this->temp_dir.'code.xml')) {
      unlink($this->temp_dir.'code.xml');
    }

    if (is_dir($this->temp_dir)) {
      rmdir($this->temp_dir);
    }
  }

  public function testGetStatisticsRebuildsLegacyScores(): void
  {
    $legacy_stats = (new ProjectCodeStatistics())
      ->setScoreAbstraction(5)
      ->setScoringVersion(CodeStatisticsParser::LEGACY_SCORING_VERSION)
    ;

    $fresh_stats = (new ProjectCodeStatistics())
      ->setScoreAbstraction(6)
      ->setScoreBonus(2)
      ->setScoringVersion(CodeStatisticsParser::CURRENT_SCORING_VERSION)
    ;

    $project = $this->createStub(Program::class);
    $project->method('getLatestCodeStatistics')->willReturn($legacy_stats);

    $parser = $this->createMock(CodeStatisticsParser::class);
    $parser->expects(self::once())
      ->method('parse')
      ->with($this->temp_dir.'code.xml')
      ->willReturn($fresh_stats)
    ;

    $repository = $this->createMock(ExtractedFileRepository::class);
    $repository->expects(self::once())
      ->method('loadProjectExtractedFile')
      ->with($project)
      ->willReturn($this->createExtractedFile())
    ;

    $statistics_repository = $this->createMock(EntityRepository::class);
    $statistics_repository->expects(self::once())
      ->method('findOneBy')
      ->with(['program' => $project], ['created_at' => 'DESC'])
      ->willReturn($legacy_stats)
    ;

    $entity_manager = $this->createMock(EntityManagerInterface::class);
    $entity_manager->expects(self::once())
      ->method('getRepository')
      ->with(ProjectCodeStatistics::class)
      ->willReturn($statistics_repository)
    ;
    $entity_manager->expects(self::once())
      ->method('persist')
      ->with($fresh_stats)
    ;
    $entity_manager->expects(self::once())
      ->method('flush')
    ;

    $service = new CodeStatisticsService(
      $parser,
      $repository,
      $entity_manager,
      $this->createStub(LoggerInterface::class),
    );

    $actual = $service->getStatistics($project);

    self::assertSame($fresh_stats, $actual);
  }

  public function testGetStatisticsFallsBackToLegacyScoresWhenRefreshFails(): void
  {
    $legacy_stats = (new ProjectCodeStatistics())
      ->setScoreAbstraction(5)
      ->setScoringVersion(CodeStatisticsParser::LEGACY_SCORING_VERSION)
    ;

    $project = $this->createStub(Program::class);
    $project->method('getLatestCodeStatistics')->willReturn($legacy_stats);

    $repository = $this->createMock(ExtractedFileRepository::class);
    $repository->expects(self::once())
      ->method('loadProjectExtractedFile')
      ->with($project)
      ->willReturn(null)
    ;

    $statistics_repository = $this->createMock(EntityRepository::class);
    $statistics_repository->expects(self::once())
      ->method('findOneBy')
      ->with(['program' => $project], ['created_at' => 'DESC'])
      ->willReturn($legacy_stats)
    ;

    $entity_manager = $this->createMock(EntityManagerInterface::class);
    $entity_manager->expects(self::once())
      ->method('getRepository')
      ->with(ProjectCodeStatistics::class)
      ->willReturn($statistics_repository)
    ;
    $entity_manager->expects(self::never())->method('persist');
    $entity_manager->expects(self::never())->method('flush');

    $service = new CodeStatisticsService(
      $this->createStub(CodeStatisticsParser::class),
      $repository,
      $entity_manager,
      $this->createStub(LoggerInterface::class),
    );

    $actual = $service->getStatistics($project);

    self::assertSame($legacy_stats, $actual);
  }

  private function createExtractedFile(): ExtractedCatrobatFile
  {
    file_put_contents($this->temp_dir.'code.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<program>
  <header>
    <programName>Service Test</programName>
    <catrobatLanguageVersion>0.998</catrobatLanguageVersion>
  </header>
</program>
XML);

    return new ExtractedCatrobatFile($this->temp_dir, '/tmp/', 'service-test');
  }
}
