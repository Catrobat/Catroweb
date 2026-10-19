<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\DBUpdater;

use App\System\Commands\DBUpdater\UpdateUserRankingCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UpdateUserRankingCommand::class)]
class UpdateUserRankingCommandTest extends TestCase
{
  public function testSmoothedScorePullsSmallSamplesTowardsTheGlobalAverage(): void
  {
    // (300 + 5 * 100) / (1 + 5) = 133.33 -> 13333 hundredths
    $this->assertSame(13333, UpdateUserRankingCommand::smoothedScore(300, 1, 100.0));
    // A creator exactly at the global average stays there regardless of project count.
    $this->assertSame(10000, UpdateUserRankingCommand::smoothedScore(200, 2, 100.0));
    $this->assertSame(10000, UpdateUserRankingCommand::smoothedScore(5000, 50, 100.0));
  }

  public function testOneLuckyProjectDoesNotOutrankAConsistentCreator(): void
  {
    $one_hit = UpdateUserRankingCommand::smoothedScore(1000, 1, 100.0);
    $consistent = UpdateUserRankingCommand::smoothedScore(20 * 400, 20, 100.0);

    $this->assertGreaterThan($one_hit, $consistent);
  }

  public function testComputeScoresUsesTheGlobalAverageAndSkipsUsersWithoutDownloads(): void
  {
    $rows = [
      ['user_id' => 'alice', 'downloads' => 200, 'projects' => 2],
      ['user_id' => 'bob', 'downloads' => '300', 'projects' => '1'],
      ['user_id' => 'carol', 'downloads' => 0, 'projects' => 1],
      ['user_id' => 'dave', 'downloads' => 100, 'projects' => 2],
    ];

    // global average: 600 downloads / 6 projects = 100
    $this->assertSame(['alice' => 10000, 'bob' => 13333, 'dave' => 8571], UpdateUserRankingCommand::computeScores($rows));
  }

  public function testComputeScoresIsDeterministic(): void
  {
    $rows = [
      ['user_id' => 'a', 'downloads' => 3, 'projects' => 2],
      ['user_id' => 'b', 'downloads' => 1, 'projects' => 1],
    ];

    $this->assertSame(UpdateUserRankingCommand::computeScores($rows), UpdateUserRankingCommand::computeScores($rows));
  }

  public function testComputeScoresWithoutProjectsIsEmpty(): void
  {
    $this->assertSame([], UpdateUserRankingCommand::computeScores([]));
  }
}
