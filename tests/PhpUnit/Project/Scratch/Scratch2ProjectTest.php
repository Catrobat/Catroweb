<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\Scratch;

use App\Project\Scratch\Converter\Scratch2Project;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Scratch2Project::class)]
final class Scratch2ProjectTest extends TestCase
{
  public function testScriptsBecomeLinkedScratch3Blocks(): void
  {
    $sprite = $this->translate([
      ['whenGreenFlag'],
      ['setVar:to:', 'score', 0],
      ['doIf', ['>', ['readVariable', 'score'], 5], [['forward:', ['*', 2, 3]], ['broadcast:', 'Go']]],
      ['lookLike:', 'cat-b'],
      ['call', 'jump %n', 10],
    ]);
    $blocks = $sprite['blocks'];
    $byOpcode = static fn (string $opcode): array => array_values(array_filter($blocks, static fn (array $b): bool => $opcode === $b['opcode']))[0];

    $hat = $byOpcode('event_whenflagclicked');
    $this->assertTrue($hat['topLevel']);
    $this->assertSame('data_setvariableto', $blocks[$hat['next']]['opcode']);
    $this->assertSame(['score', 'sprite1-v-score'], $byOpcode('data_setvariableto')['fields']['VARIABLE']);

    $if = $byOpcode('control_if');
    $this->assertSame('operator_gt', $blocks[$if['inputs']['CONDITION'][1]]['opcode']);
    $move = $blocks[$if['inputs']['SUBSTACK'][1]];
    $this->assertSame('motion_movesteps', $move['opcode']);
    $this->assertSame('operator_multiply', $blocks[$move['inputs']['STEPS'][1]]['opcode']); // a reporter obscuring the number
    $this->assertSame([4, 10], $move['inputs']['STEPS'][2]);
    $this->assertSame([11, 'Go', 'msg-go'], $blocks[$move['next']]['inputs']['BROADCAST_INPUT'][1]);

    $costume = $byOpcode('looks_switchcostumeto')['inputs']['COSTUME'];
    $this->assertSame(['cat-b', null], $blocks[$costume[1]]['fields']['COSTUME']);

    $call = $byOpcode('procedures_call');
    $this->assertSame('jump %n', $call['mutation']['proccode']);
    $this->assertSame([1, [4, 10]], $call['inputs']['input0']);
  }

  public function testAProcedureDefinitionCarriesItsPrototype(): void
  {
    $blocks = $this->translate([['procDef', 'jump %n %b', ['height', 'fast'], [10, false], true]])['blocks'];
    $definition = array_values(array_filter($blocks, static fn (array $b): bool => 'procedures_definition' === $b['opcode']))[0];
    $prototype = $blocks[$definition['inputs']['custom_block'][1]];

    $this->assertSame('jump %n %b', $prototype['mutation']['proccode']);
    $this->assertSame('["input0","input1"]', $prototype['mutation']['argumentids']);
    $this->assertSame('["height","fast"]', $prototype['mutation']['argumentnames']);
  }

  public function testSpritePropertiesAndAssetsAreRenamed(): void
  {
    $project = Scratch2Project::toScratch3(['objName' => 'Stage', 'costumes' => [['costumeName' => 'bg', 'baseLayerMD5' => 'aa.png', 'bitmapResolution' => 2]], 'children' => [
      ['objName' => 'Cat', 'scratchX' => 10, 'scratchY' => -5, 'scale' => 0.5, 'direction' => 270, 'rotationStyle' => 'leftRight',
        'costumes' => [['costumeName' => 'c', 'baseLayerMD5' => 'bb.svg', 'rotationCenterX' => 3, 'rotationCenterY' => 4]],
        'sounds' => [['soundName' => 'meow', 'md5' => 'cc.wav']]],
      ['target' => 'Cat', 'cmd' => 'getVar:'], // a watcher, not a sprite
    ]]);
    [$stage, $cat] = $project['targets'];

    $this->assertSame(480, $stage['costumes'][0]['rotationCenterX']);
    $this->assertSame([10, -5, 50.0, -90.0, 'left-right'], [$cat['x'], $cat['y'], $cat['size'], $cat['direction'], $cat['rotationStyle']]);
    $this->assertSame(['bb.svg', 'svg'], [$cat['costumes'][0]['md5ext'], $cat['costumes'][0]['dataFormat']]);
    $this->assertSame('cc.wav', $cat['sounds'][0]['md5ext']);
    $this->assertCount(2, $project['targets']);
  }

  /**
   * @param list<mixed> $script
   *
   * @return array<string, mixed> the sprite target
   */
  private function translate(array $script): array
  {
    $project = Scratch2Project::toScratch3([
      'objName' => 'Stage',
      'children' => [['objName' => 'Cat', 'variables' => [['name' => 'score', 'value' => 0]], 'scripts' => [[0, 0, $script]]]],
    ]);

    return $project['targets'][1];
  }
}
