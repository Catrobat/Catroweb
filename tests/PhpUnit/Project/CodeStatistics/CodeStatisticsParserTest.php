<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CodeStatistics;

use App\Project\CodeStatistics\CodeStatisticsParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CodeStatisticsParser::class)]
class CodeStatisticsParserTest extends TestCase
{
  private CodeStatisticsParser $parser;

  private string $fixtures_path;

  #[\Override]
  protected function setUp(): void
  {
    $this->parser = new CodeStatisticsParser();
    $this->fixtures_path = __DIR__.'/Fixtures/';
  }

  public function testParseSampleProject(): void
  {
    $stats = $this->parser->parse($this->fixtures_path.'sample_code.xml');

    // No <scene> tags in sample project (non-scene project)
    self::assertSame(0, $stats->getScenes());

    // 7 scripts total: 2 StartScript, 1 BroadcastScript, 1 WhenScript, 1 WhenClonedScript, 1 WhenTouchDownScript, 1 UserDefinedScript
    self::assertSame(7, $stats->getScripts());

    // Verify script type counts
    $script_counts = $stats->getScriptCounts();
    self::assertSame(2, $script_counts['StartScript']);
    self::assertSame(1, $script_counts['BroadcastScript']);
    self::assertSame(1, $script_counts['WhenScript']);
    self::assertSame(1, $script_counts['WhenClonedScript']);
    self::assertSame(1, $script_counts['WhenTouchDownScript']);
    self::assertSame(1, $script_counts['UserDefinedScript']);

    // Count bricks
    $brick_counts = $stats->getBrickCounts();
    self::assertSame(26, $stats->getBricks());

    // Verify specific brick counts
    self::assertSame(1, $brick_counts['SetVariableBrick']);
    self::assertSame(1, $brick_counts['ChangeVariableBrick']);
    self::assertSame(1, $brick_counts['BroadcastBrick']);
    self::assertSame(1, $brick_counts['PlaySoundBrick']);
    self::assertSame(1, $brick_counts['WaitBrick']);
    self::assertSame(1, $brick_counts['PlaceAtBrick']);
    self::assertSame(1, $brick_counts['SetXBrick']);
    self::assertSame(1, $brick_counts['ForeverBrick']);
    self::assertSame(1, $brick_counts['MoveNStepsBrick']);
    self::assertSame(1, $brick_counts['IfLogicBeginBrick']);
    self::assertSame(1, $brick_counts['IfLogicElseBrick']);
    self::assertSame(1, $brick_counts['SetLookBrick']);
    self::assertSame(1, $brick_counts['HideBrick']);
    self::assertSame(2, $brick_counts['LoopEndBrick']);

    // 3 objects: Background, Cat, Item1 (SingleSprite + GroupItemSprite)
    self::assertSame(3, $stats->getObjects());

    // 4 looks total
    self::assertSame(4, $stats->getLooks());

    // 3 sounds total
    self::assertSame(3, $stats->getSounds());

    // 3 global variables (2 in programVariableList + 1 in programListOfLists)
    self::assertSame(3, $stats->getGlobalVariables());

    // 3 local variables (in objectVariableList)
    self::assertSame(3, $stats->getLocalVariables());
  }

  public function testComputationalThinkingScores(): void
  {
    $stats = $this->parser->parse($this->fixtures_path.'sample_code.xml');

    // Abstraction: UserDefinedScript(1) + UserDefinedBrick(1) + CloneBrick(1) + DeleteThisCloneBrick(1) + WhenClonedScript(1) = 5
    self::assertSame(5, $stats->getScoreAbstraction());

    // Parallelism: BroadcastScript(1) + WhenClonedScript(1) = 2 (only script types in PARALLELISM_SCRIPTS)
    self::assertSame(2, $stats->getScoreParallelism());

    // Synchronization: BroadcastBrick(1) + WaitBrick(1) = 2
    self::assertSame(2, $stats->getScoreSynchronization());

    // Logical thinking: IfLogicBeginBrick(1) + IfLogicElseBrick(1) = 2
    self::assertSame(2, $stats->getScoreLogicalThinking());

    // Flow control: ForeverBrick(1) + RepeatBrick(1) = 2
    self::assertSame(2, $stats->getScoreFlowControl());

    // User interactivity: WhenScript(1) + WhenTouchDownScript(1) + AskBrick(1) = 3
    self::assertSame(3, $stats->getScoreUserInteractivity());

    // Data representation: SetVariableBrick(1) + ChangeVariableBrick(1) + AddItemToUserListBrick(1) = 3
    self::assertSame(3, $stats->getScoreDataRepresentation());
  }

  public function testParseEmptyProject(): void
  {
    $stats = $this->parser->parse($this->fixtures_path.'empty_project.xml');

    self::assertSame(0, $stats->getScenes());
    self::assertSame(0, $stats->getScripts());
    self::assertSame(0, $stats->getBricks());
    self::assertSame(1, $stats->getObjects());
    self::assertSame(0, $stats->getLooks());
    self::assertSame(0, $stats->getSounds());
    self::assertSame(0, $stats->getGlobalVariables());
    self::assertSame(0, $stats->getLocalVariables());
    self::assertSame([], $stats->getScriptCounts());
    self::assertSame([], $stats->getBrickCounts());
    self::assertSame(0, $stats->getScoreAbstraction());
    self::assertSame(0, $stats->getScoreFlowControl());
  }

  public function testParseSceneProject(): void
  {
    $stats = $this->parser->parse($this->fixtures_path.'scene_project.xml');

    // 2 scenes
    self::assertSame(2, $stats->getScenes());

    // 2 scripts: StartScript, WhenScript
    self::assertSame(2, $stats->getScripts());

    // 5 bricks: SetXBrick, BroadcastWaitBrick, RepeatUntilBrick, SetVariableBrick, LoopEndBrick
    self::assertSame(5, $stats->getBricks());

    // 2 objects: Background (Scene 1) + Player (Scene 2)
    self::assertSame(2, $stats->getObjects());

    // 2 looks
    self::assertSame(2, $stats->getLooks());

    // 1 sound
    self::assertSame(1, $stats->getSounds());

    // 1 global variable
    self::assertSame(1, $stats->getGlobalVariables());

    // Flow control score: RepeatUntilBrick(1) = 1
    self::assertSame(1, $stats->getScoreFlowControl());

    // Synchronization score: BroadcastWaitBrick(1) = 1
    self::assertSame(1, $stats->getScoreSynchronization());

    // Data representation: SetVariableBrick(1) = 1
    self::assertSame(1, $stats->getScoreDataRepresentation());
  }

  public function testParseNonexistentFile(): void
  {
    $stats = $this->parser->parse('/nonexistent/path/code.xml');

    self::assertSame(0, $stats->getScripts());
    self::assertSame(0, $stats->getBricks());
    self::assertSame(0, $stats->getObjects());
  }

  public function testUnknownBrickTypesAreCounted(): void
  {
    $stats = $this->parser->parse($this->fixtures_path.'sample_code.xml');

    // All brick types should be present in brick_counts - none should be "unknown"
    $brick_counts = $stats->getBrickCounts();
    self::assertArrayNotHasKey('unknown', $brick_counts);

    // Total bricks should equal sum of all typed brick counts
    $sum = array_sum($brick_counts);
    self::assertSame($stats->getBricks(), $sum);
  }

  public function testScriptCountsConsistentWithTotal(): void
  {
    $stats = $this->parser->parse($this->fixtures_path.'sample_code.xml');

    $script_counts = $stats->getScriptCounts();
    $sum = array_sum($script_counts);
    self::assertSame($stats->getScripts(), $sum);
  }
}
