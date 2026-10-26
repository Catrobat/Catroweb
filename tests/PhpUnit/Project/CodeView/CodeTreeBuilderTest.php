<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CodeView;

use App\DB\Entity\Project\Project;
use App\Project\CodeView\CodeExportRunner;
use App\Project\CodeView\CodeTreeBuilder;
use App\Project\CodeView\CodeTreeBuildException;
use App\Project\CodeView\CodeViewExportStore;
use App\Project\CodeView\CodeViewLocalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[CoversClass(CodeTreeBuilder::class)]
class CodeTreeBuilderTest extends TestCase
{
  private const array EXPORT = [
    'files_in_scene_folders' => true,
    'scenes' => [[
      'name' => 'Scene 1',
      'objects' => [
        [
          'name' => 'Cat',
          'looks' => [['name' => 'cat', 'file' => 'cat 1.png']],
          'sounds' => [['name' => 'meow', 'file' => 'meow.mp3']],
          'scripts' => [[
            'type' => 'StartScript',
            'category' => 'event',
            'commented_out' => false,
            'parts' => [['kind' => 'label', 'text' => "\x01s:brick_when_started\x02"]],
            'bricks' => [[
              'type' => 'IfLogicBeginBrick',
              'category' => 'control',
              'commented_out' => true,
              'parts' => [['kind' => 'label', 'text' => 'If'], ['kind' => 'formula', 'text' => '1 < 2']],
              'sections' => [
                ['bricks' => [['type' => 'WaitBrick', 'category' => 'control', 'parts' => [['kind' => 'label', 'text' => "\x01s:brick_wait\x02"]]]]],
                ['parts' => [['kind' => 'label', 'text' => 'Else']], 'bricks' => []],
                ['parts' => [['kind' => 'label', 'text' => 'End if']], 'bricks' => []],
              ],
            ]],
          ]],
        ],
        ['name' => 'Group', 'looks' => [], 'sounds' => [], 'scripts' => [], 'group' => [
          ['name' => 'Member', 'looks' => [], 'sounds' => [], 'scripts' => []],
        ]],
      ],
    ]],
  ];

  public function testTheCodeViewInTheViewersLanguage(): void
  {
    $tree = $this->builder(self::EXPORT)->buildCodeTree($this->project(), 'de');

    $cat = $tree['scenes'][0]['objects'][0];
    $this->assertSame('Cat', $cat['name']);
    $this->assertFalse($cat['is_group']);
    $script = $cat['scripts'][0];
    $this->assertSame([['kind' => 'label', 'text' => 'When scene starts']], $script['parts']);
    $if = $script['bricks'][0];
    $this->assertSame('IfLogicBeginBrick', $if['type']);
    $this->assertTrue($if['commented_out']);
    $this->assertNull($if['bricks']);
    $this->assertSame('Warte', $if['sections'][0]['bricks'][0]['parts'][0]['text']);
    $this->assertSame('Else', $if['sections'][1]['parts'][0]['text']);
    $this->assertCount(3, $if['sections']);
  }

  public function testLooksSoundsAndGroups(): void
  {
    $tree = $this->builder(self::EXPORT)->buildCodeTree($this->project(), 'en');

    $cat = $tree['scenes'][0]['objects'][0];
    $this->assertSame('/project-files/p1/assets/Scene%201/images/cat%201.png', $cat['looks'][0]['url']);
    $this->assertSame('/project-files/p1/assets/Scene%201/sounds/meow.mp3', $cat['sounds'][0]['url']);
    $group = $tree['scenes'][0]['objects'][1];
    $this->assertTrue($group['is_group']);
    $this->assertSame('Member', $group['children'][0]['name']);
  }

  public function testProjectsWithoutSceneFoldersKeepFilesAtTheTop(): void
  {
    $tree = $this->builder(['files_in_scene_folders' => false] + self::EXPORT)->buildCodeTree($this->project(), 'en');

    $this->assertSame('/project-files/p1/assets/images/cat%201.png', $tree['scenes'][0]['objects'][0]['looks'][0]['url']);
  }

  public function testAProjectWhoseCodeCannotBeShown(): void
  {
    $this->expectException(CodeTreeBuildException::class);
    $this->builder(['error' => 'code.xml is not valid XML'])->buildCodeTree($this->project());
  }

  public function testAProjectWithoutFiles(): void
  {
    $this->expectException(CodeTreeBuildException::class);
    $this->builder(null)->buildCodeTree($this->project());
  }

  /**
   * @param array<string, mixed>|null $export
   */
  private function builder(?array $export): CodeTreeBuilder
  {
    $store = $this->createStub(CodeViewExportStore::class);
    $store->method('get')->willReturn($export);
    $requests = new RequestStack();
    $requests->push(Request::create('/api/projects/p1/code'));

    return new CodeTreeBuilder($store, new CodeViewLocalizer(new CodeExportRunner('tests/TestData/CodeExport', new NullLogger())), $requests);
  }

  private function project(): Project
  {
    $project = new Project();
    $project->setId('p1');

    return $project;
  }
}
