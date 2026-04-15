<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CodeView;

use App\Project\CatrobatCode\Parser\CatrobatCodeParser;
use App\Project\CatrobatCode\Parser\Constants;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CodeView\CodeTreeBuilder;
use App\Project\CodeView\CodeTreeBuildException;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(CodeTreeBuilder::class)]
class CodeTreeBuilderTest extends TestCase
{
  private CodeTreeBuilder $builder;

  #[\Override]
  protected function setUp(): void
  {
    $extracted_file_repository = $this->createStub(ExtractedFileRepository::class);
    $this->builder = new CodeTreeBuilder(
      $extracted_file_repository,
      new CatrobatCodeParser(),
    );
  }

  public function testSimpleProjectReturnsOneScene(): void
  {
    $extracted = new ExtractedCatrobatFile(
      BootstrapExtension::$FIXTURES_DIR.'CodeView/SimpleProject/',
      '',
      ''
    );

    $tree = $this->builder->buildFromExtracted($extracted);

    $this->assertNotEmpty($tree['scenes']);
    $this->assertCount(1, $tree['scenes']);

    $scene = $tree['scenes'][0];
    $this->assertSame('default', $scene['name']);
    $this->assertCount(2, $scene['objects']);
  }

  public function testSimpleProjectBackgroundObject(): void
  {
    $extracted = new ExtractedCatrobatFile(
      BootstrapExtension::$FIXTURES_DIR.'CodeView/SimpleProject/',
      '',
      ''
    );

    $tree = $this->builder->buildFromExtracted($extracted);
    $background = $tree['scenes'][0]['objects'][0];

    $this->assertSame('Background', $background['name']);
    $this->assertFalse($background['is_group']);
    $this->assertCount(1, $background['scripts']);

    $script = $background['scripts'][0];
    $this->assertSame(Constants::START_SCRIPT, $script['type']);
    $this->assertSame('event', $script['category']);
    $this->assertFalse($script['commented_out']);
    $this->assertCount(1, $script['bricks']);

    $brick = $script['bricks'][0];
    $this->assertSame(Constants::WAIT_BRICK, $brick['type']);
    $this->assertSame('control', $brick['category']);
    $this->assertNull($brick['children']);
    $this->assertIsArray($brick['parameters']);
    $this->assertArrayHasKey('TIME_TO_WAIT_IN_SECONDS', $brick['parameters']);
    $this->assertSame('1', $brick['parameters']['TIME_TO_WAIT_IN_SECONDS']);
  }

  public function testSimpleProjectSpriteWithMultipleBricks(): void
  {
    $extracted = new ExtractedCatrobatFile(
      BootstrapExtension::$FIXTURES_DIR.'CodeView/SimpleProject/',
      '',
      ''
    );

    $tree = $this->builder->buildFromExtracted($extracted);
    $sprite = $tree['scenes'][0]['objects'][1];

    $this->assertSame('Sprite1', $sprite['name']);
    $this->assertCount(1, $sprite['scripts']);
    $this->assertCount(3, $sprite['scripts'][0]['bricks']);

    $bricks = $sprite['scripts'][0]['bricks'];

    // PlaceAtBrick with X and Y parameters
    $this->assertSame(Constants::PLACE_AT_BRICK, $bricks[0]['type']);
    $this->assertSame('motion', $bricks[0]['category']);
    $this->assertNotNull($bricks[0]['parameters']);
    $this->assertSame('100', $bricks[0]['parameters']['X_POSITION']);
    $this->assertSame('200', $bricks[0]['parameters']['Y_POSITION']);
    $this->assertNull($bricks[0]['children']);

    // ShowBrick - no parameters, no children
    $this->assertSame(Constants::SHOW_BRICK, $bricks[1]['type']);
    $this->assertSame('looks', $bricks[1]['category']);
    $this->assertNull($bricks[1]['parameters']);
    $this->assertNull($bricks[1]['children']);

    // HideBrick
    $this->assertSame(Constants::HIDE_BRICK, $bricks[2]['type']);
  }

  public function testForeverLoopNesting(): void
  {
    $extracted = new ExtractedCatrobatFile(
      BootstrapExtension::$FIXTURES_DIR.'CodeView/NestedControlProject/',
      '',
      ''
    );

    $tree = $this->builder->buildFromExtracted($extracted);
    $sprite = $tree['scenes'][0]['objects'][1];
    /** @var list<array<string, mixed>> $bricks */
    $bricks = $sprite['scripts'][0]['bricks'];

    // ForeverBrick with loop_body children
    $this->assertCount(1, $bricks);
    $forever = $bricks[0];
    $this->assertSame(Constants::FOREVER_BRICK, $forever['type']);
    $this->assertSame('control', $forever['category']);
    $this->assertIsArray($forever['children']);
    $this->assertArrayHasKey('loop_body', $forever['children']);

    /** @var list<array<string, mixed>> $loop_body */
    $loop_body = $forever['children']['loop_body'];
    $this->assertCount(2, $loop_body);
    $this->assertSame(Constants::MOVE_N_STEPS_BRICK, $loop_body[0]['type']);
    $this->assertSame(Constants::WAIT_BRICK, $loop_body[1]['type']);
  }

  public function testIfElseNesting(): void
  {
    $extracted = new ExtractedCatrobatFile(
      BootstrapExtension::$FIXTURES_DIR.'CodeView/NestedControlProject/',
      '',
      ''
    );

    $tree = $this->builder->buildFromExtracted($extracted);
    $sprite = $tree['scenes'][0]['objects'][1];
    $bricks = $sprite['scripts'][1]['bricks'];

    // IfLogicBeginBrick with if_branch and else_branch
    $if_brick = $bricks[0];
    $this->assertSame(Constants::IF_BRICK, $if_brick['type']);
    $this->assertIsArray($if_brick['children']);
    $this->assertArrayHasKey('if_branch', $if_brick['children']);
    $this->assertArrayHasKey('else_branch', $if_brick['children']);

    $this->assertCount(1, $if_brick['children']['if_branch']);
    $this->assertSame(Constants::SHOW_BRICK, $if_brick['children']['if_branch'][0]['type']);

    $this->assertCount(1, $if_brick['children']['else_branch']);
    $this->assertSame(Constants::HIDE_BRICK, $if_brick['children']['else_branch'][0]['type']);
  }

  public function testIfThenWithoutElse(): void
  {
    $extracted = new ExtractedCatrobatFile(
      BootstrapExtension::$FIXTURES_DIR.'CodeView/NestedControlProject/',
      '',
      ''
    );

    $tree = $this->builder->buildFromExtracted($extracted);
    $sprite = $tree['scenes'][0]['objects'][1];
    $bricks = $sprite['scripts'][1]['bricks'];

    // IfThenLogicBeginBrick in XML, but BrickFactory normalizes to IF_BRICK type
    $if_then = $bricks[1];
    $this->assertSame(Constants::IF_BRICK, $if_then['type']);
    $this->assertIsArray($if_then['children']);
    $this->assertArrayHasKey('if_branch', $if_then['children']);
    $this->assertArrayNotHasKey('else_branch', $if_then['children']);

    $this->assertCount(1, $if_then['children']['if_branch']);
    $this->assertSame(Constants::PLACE_AT_BRICK, $if_then['children']['if_branch'][0]['type']);
  }

  public function testMultiSceneProject(): void
  {
    $extracted = new ExtractedCatrobatFile(
      BootstrapExtension::$FIXTURES_DIR.'CodeView/SceneProject/',
      '',
      ''
    );

    $tree = $this->builder->buildFromExtracted($extracted);

    $this->assertCount(2, $tree['scenes']);

    $scene1 = $tree['scenes'][0];
    $this->assertSame('Scene 1', $scene1['name']);
    $this->assertCount(2, $scene1['objects']);
    $this->assertSame('Cat', $scene1['objects'][1]['name']);

    $scene2 = $tree['scenes'][1];
    $this->assertSame('Scene 2', $scene2['name']);
    $this->assertCount(2, $scene2['objects']);
    $this->assertSame('Dog', $scene2['objects'][1]['name']);
  }

  public function testMissingFileThrowsException(): void
  {
    $this->expectException(CodeTreeBuildException::class);

    $extracted_file_repository = $this->createStub(ExtractedFileRepository::class);
    $extracted_file_repository->method('loadProjectExtractedFile')->willReturn(null);

    $builder = new CodeTreeBuilder(
      $extracted_file_repository,
      new CatrobatCodeParser(),
    );

    $project = $this->createStub(\App\DB\Entity\Project\Project::class);
    $builder->buildCodeTree($project);
  }

  public function testInvalidXmlThrowsException(): void
  {
    $this->expectException(\App\Project\CatrobatFile\InvalidCatrobatFileException::class);

    // This directory doesn't exist, so ExtractedCatrobatFile constructor will throw
    new ExtractedCatrobatFile(
      BootstrapExtension::$FIXTURES_DIR.'CodeView/NonExistentProject/',
      '',
      ''
    );
  }

  public function testBrickDisplayTextSubstitution(): void
  {
    $extracted = new ExtractedCatrobatFile(
      BootstrapExtension::$FIXTURES_DIR.'CodeView/SimpleProject/',
      '',
      ''
    );

    $tree = $this->builder->buildFromExtracted($extracted);
    $sprite = $tree['scenes'][0]['objects'][1];
    $place_at = $sprite['scripts'][0]['bricks'][0];

    // PlaceAtBrick caption is "Place at X: _ Y: _", with formulas substituted
    $this->assertStringContainsString('100', $place_at['display_text']);
    $this->assertStringContainsString('200', $place_at['display_text']);
  }

  public function testCommentedOutFlag(): void
  {
    $extracted = new ExtractedCatrobatFile(
      BootstrapExtension::$FIXTURES_DIR.'CodeView/SimpleProject/',
      '',
      ''
    );

    $tree = $this->builder->buildFromExtracted($extracted);
    $sprite = $tree['scenes'][0]['objects'][1];

    // None of the bricks in this fixture are commented out
    foreach ($sprite['scripts'] as $script) {
      $this->assertFalse($script['commented_out']);
      foreach ($script['bricks'] as $brick) {
        $this->assertFalse($brick['commented_out']);
      }
    }
  }
}
