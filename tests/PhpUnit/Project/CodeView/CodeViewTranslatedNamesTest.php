<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CodeView;

use App\Project\CodeView\CodeViewTranslatedNames;
use App\Project\CodeView\CodeViewTranslationContent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/** @internal */
#[CoversClass(CodeViewTranslatedNames::class)]
#[CoversClass(CodeViewTranslationContent::class)]
final class CodeViewTranslatedNamesTest extends TestCase
{
  public function testIdenticalLocalNamesKeepTheirOwnIdentity(): void
  {
    $export = [
      'symbols' => [
        $this->symbol('name-0', 'sprite-0', 'puntos'),
        $this->symbol('name-1', 'sprite-1', 'puntos'),
      ],
      'segments' => [
        ['kind' => 'variable', 'symbol' => 'name-0', 'source' => 'puntos'],
        ['kind' => 'variable', 'symbol' => 'name-0', 'source' => 'puntos'],
        ['kind' => 'variable', 'symbol' => 'name-1', 'source' => 'puntos'],
      ],
    ];

    $this->assertSame(['name-0' => 'puntos', 'name-1' => 'puntos'], CodeViewTranslationContent::texts($export));
    $this->assertSame([], CodeViewTranslationContent::texts($export, include_names: false));
    $this->assertSame(
      [['key' => 'name-0', 'text' => 'Punkte'], ['key' => 'name-1', 'text' => 'Punkte']],
      CodeViewTranslatedNames::updates($export, ['name-0' => 'Punkte', 'name-1' => 'Punkte'])
    );
  }

  public function testAliasesCollideWithinOneScopeAndWithUntranslatedProjectNames(): void
  {
    $export = ['symbols' => [
      $this->symbol('name-0', 'sprite-0', 'puntos'),
      $this->symbol('name-1', 'sprite-0', 'marcador'),
      $this->symbol('name-2', 'project', 'Score'),
      $this->symbol('name-3', 'sprite-1', 'Wertung'),
      $this->symbol('name-4', 'sprite-1', 'Leben'),
    ]];

    $this->assertSame(
      [['key' => 'name-4', 'text' => 'Lives']],
      CodeViewTranslatedNames::updates($export, [
        'name-0' => 'Points',
        'name-1' => 'points',
        'name-3' => 'Score',
        'name-4' => 'Lives',
        'name-5' => 'Unresolved',
      ])
    );
  }

  private function symbol(string $id, string $scope, string $name): array
  {
    return ['id' => $id, 'kind' => 'variable', 'scope' => $scope, 'name' => $name];
  }
}
