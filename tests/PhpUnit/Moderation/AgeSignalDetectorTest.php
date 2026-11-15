<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Moderation;

use App\Moderation\AgeSignalDetector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AgeSignalDetector::class)]
#[Group('unit')]
class AgeSignalDetectorTest extends TestCase
{
  private AgeSignalDetector $detector;

  #[\Override]
  protected function setUp(): void
  {
    $this->detector = new AgeSignalDetector();
  }

  #[DataProvider('signalProvider')]
  public function testDetectsSignal(string $text): void
  {
    $this->assertNotNull($this->detector->findSignal($text), $text);
  }

  /**
   * @return array<string, array{string}>
   */
  public static function signalProvider(): array
  {
    return [
      'plain 18+' => ['Contains 18+ content'],
      'plus first' => ['+18 projects'],
      'spaced' => ['only 18 + here'],
      'plus with space' => ['studio + 18'],
      'at the end' => ['my game 18+'],
      'followed by comma' => ['warning: +18, sorry'],
      'cyrillic text' => ['18+ контент'],
      'eighteen plus' => ['18 plus only'],
      'and over' => ['for people 18 and over'],
      'nsfw' => ['NSFW stuff'],
      'adults only' => ['adults only!'],
      'only adults' => ['only adults allowed'],
      'adult content' => ['Adult content inside'],
      'r18' => ['R18 art'],
      'r-18' => ['r-18 game'],
      'fsk' => ['FSK 18 Spiel'],
      'de ab 18' => ['Ab 18 Jahren'],
      'de erwachsene' => ['Nur für Erwachsene'],
      'es solo adultos' => ['Solo adultos'],
      'es mayores' => ['para mayores de 18'],
      'es contenido' => ['contenido para adultos'],
      'pt so adultos' => ['só para adultos'],
      'pt maiores' => ['maiores de 18'],
      'pt conteudo' => ['conteúdo adulto'],
      'ru only adults' => ['только для взрослых'],
      'ru older' => ['старше 18'],
      'fr' => ['réservé aux adultes'],
      'zero-width space' => ["18\u{200B}+"],
    ];
  }

  public function testChecksEveryText(): void
  {
    $this->assertSame('+18', $this->detector->findSignal('My studio', null, '', '+18 projects'));
  }

  #[DataProvider('harmlessProvider')]
  public function testIgnoresHarmlessText(?string $text): void
  {
    $this->assertNull($this->detector->findSignal($text), (string) $text);
  }

  /**
   * @return array<string, array{string|null}>
   */
  public static function harmlessProvider(): array
  {
    return [
      'null' => [null],
      'empty' => [''],
      'version number' => ['needs Catroid 1.18+'],
      'version word' => ['works on version 18+'],
      'v prefix' => ['v18+ only'],
      'level' => ['reach level 18'],
      'level plus' => ['level 18+ unlocks the boss'],
      'android' => ['Android 18+'],
      'year' => ['made in 2018'],
      'larger number' => ['118+ levels'],
      'sum' => ['what is 18+2?'],
      'spaced sum' => ['1 + 18 = 19'],
      'score' => ['+18 points per coin'],
      'phone like' => ['+18005551234'],
      'time de' => ['Treffen ab 18 Uhr'],
      'time colon' => ['ab 18:30'],
      'decimal' => ['score 3.18+'],
      'age normal' => ['I am 12 years old'],
      'words containing nsfw letters' => ['transfwd'],
      'adult word alone' => ['ask an adult for help'],
      'kids game' => ['A fun game for kids'],
    ];
  }
}
