<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\Scratch;

use App\Project\Scratch\Converter\AssetNormalizer;
use App\Project\Scratch\Converter\ScratchConversionException;
use App\Project\Scratch\Converter\ScratchToCatrobatConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Converts a small offline Scratch project and checks what Pocket Code needs to load it.
 *
 * @internal
 */
#[CoversClass(ScratchToCatrobatConverter::class)]
final class ScratchToCatrobatConverterTest extends TestCase
{
  private const string FIXTURES = __DIR__.'/../../../TestData/ScratchConverter/';

  private \DOMDocument $xml;

  private \ZipArchive $zip;

  /** @var array<string, int> */
  private array $unsupported;

  #[\Override]
  protected function setUp(): void
  {
    foreach (['rsvg-convert', 'sox'] as $tool) {
      if (null === (new ExecutableFinder())->find($tool)) {
        $this->markTestSkipped($tool.' is not installed (it is in the app image).');
      }
    }

    $project = json_decode((string) file_get_contents(self::FIXTURES.'project.json'), true, 512, JSON_THROW_ON_ERROR);
    $assets = [];
    foreach ((array) glob(self::FIXTURES.'*.{svg,png,wav}', GLOB_BRACE) as $file) {
      $assets[basename((string) $file)] = (string) file_get_contents((string) $file);
    }

    $archive = tempnam(sys_get_temp_dir(), 'scratch-test');
    $metadata = ['title' => 'Fixture', 'author' => ['username' => 'tester'], 'description' => 'A test'];
    $this->unsupported = (new ScratchToCatrobatConverter(new AssetNormalizer()))->convert(42, $project, $metadata, $assets, null, (string) $archive);

    $this->zip = new \ZipArchive();
    $this->assertTrue($this->zip->open((string) $archive));
    $this->xml = new \DOMDocument();
    $code = $this->zip->getFromName('code.xml');
    if (!\is_string($code) || '' === $code) {
      $this->fail('The archive has no code.xml');
    }

    $this->assertTrue($this->xml->loadXML($code));
  }

  public function testEveryReferencePointsBackToAFullObject(): void
  {
    $xpath = new \DOMXPath($this->xml);
    $references = $xpath->query('//*[@reference]');
    $this->assertNotFalse($references);
    $this->assertGreaterThan(5, $references->length);

    foreach ($references as $reference) {
      $this->assertInstanceOf(\DOMElement::class, $reference);
      $target = $reference;
      foreach (explode('/', $reference->getAttribute('reference')) as $step) {
        $target = '..' === $step ? $target->parentNode : self::child($target, $step);
        $this->assertInstanceOf(\DOMElement::class, $target, 'Dangling reference '.$reference->getAttribute('reference'));
      }

      $this->assertFalse($target->hasAttribute('reference'), 'Reference to a reference');
      $this->assertSame(\DOMNode::DOCUMENT_POSITION_PRECEDING, $reference->compareDocumentPosition($target) & \DOMNode::DOCUMENT_POSITION_PRECEDING, 'Forward reference');
    }
  }

  public function testEveryLookAndSoundHasItsFile(): void
  {
    foreach (['look' => 'images', 'sound' => 'sounds'] as $tag => $folder) {
      foreach ($this->xml->getElementsByTagName($tag) as $element) {
        if ($element->hasAttribute('fileName')) {
          $this->assertNotFalse($this->zip->locateName('Scene/'.$folder.'/'.$element->getAttribute('fileName')));
        }
      }
    }
  }

  public function testHeaderMatchesTheScratchStage(): void
  {
    $header = fn (string $name): string => (string) $this->xml->getElementsByTagName($name)->item(0)?->textContent;
    $this->assertSame('1.14', $header('catrobatLanguageVersion'));
    $this->assertSame('480', $header('screenWidth'));
    $this->assertSame('360', $header('screenHeight'));
    $this->assertSame('Fixture', $header('programName'));
    $this->assertSame('https://scratch.mit.edu/projects/42', $header('remixOf'));
  }

  public function testScriptsAndBricksAreTranslated(): void
  {
    $types = [];
    foreach ((new \DOMXPath($this->xml))->query('//brick/@type|//script/@type') ?: [] as $attribute) {
      $types[$attribute->nodeValue] = true;
    }

    foreach (['StartScript', 'BroadcastScript', 'UserDefinedScript', 'WhenClonedScript', 'ForeverBrick', 'MoveNStepsBrick',
      'IfThenLogicBeginBrick', 'SayBubbleBrick', 'PlaySoundBrick', 'SetLookByIndexBrick', 'SetXBrick', 'AddItemToUserListBrick',
      'UserDefinedBrick', 'ChangeYByNBrick', 'CloneBrick', 'PlaceAtBrick', 'SetSizeToBrick', 'NoteBrick'] as $type) {
      $this->assertArrayHasKey($type, $types, $type.' missing');
    }

    $this->assertSame([], $this->unsupported);
    $this->assertStringContainsString('fisheye', (string) (new \DOMXPath($this->xml))->evaluate('string(//brick[@type="NoteBrick"])'));
  }

  public function testNegativeNumbersAreUnaryMinus(): void
  {
    foreach ($this->xml->getElementsByTagName('value') as $value) {
      $this->assertDoesNotMatchRegularExpression('/^-\d/', $value->textContent);
    }
  }

  public function testAnOperatorInsideATighterOperatorIsBracketed(): void
  {
    // (score + 1) * 2 must keep its parentheses when Pocket Code turns the tree back into text.
    $brackets = (new \DOMXPath($this->xml))->query('//*[type="OPERATOR" and value="MULT"]/leftChild[type="BRACKET"]/rightChild[value="PLUS"]');
    $this->assertNotFalse($brackets);
    $this->assertSame(1, $brackets->length);
  }

  public function testCostumesArePaddedAroundTheirRotationCenter(): void
  {
    // The SVG's viewBox starts at (-10, -10); its rotation center (0, 0) sits 10px in from the top-left of a 40x30 image.
    $looks = (new \DOMXPath($this->xml))->query('//object[@name="Ball"]/lookList/look[@name="ball"]');
    $this->assertNotFalse($looks);
    $look = $looks->item(0);
    $this->assertInstanceOf(\DOMElement::class, $look);
    $size = getimagesizefromstring((string) $this->zip->getFromName('Scene/images/'.$look->getAttribute('fileName')));
    $this->assertSame([60, 40], [$size[0] ?? 0, $size[1] ?? 0]);
  }

  public function testAHugeVectorCostumeIsRejectedBeforeRendering(): void
  {
    $this->expectException(ScratchConversionException::class);
    (new AssetNormalizer())->costumeToPng('<svg xmlns="http://www.w3.org/2000/svg" width="20000" height="20000"/>', ['dataFormat' => 'svg'], microtime(true) + 10.0);
  }

  public function testABlackAndWhiteCostumeStaysRgba(): void
  {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="4" height="4"><rect width="4" height="4" fill="#000"/></svg>';
    $png = (new AssetNormalizer())->costumeToPng($svg, ['dataFormat' => 'svg', 'rotationCenterX' => 2, 'rotationCenterY' => 2], microtime(true) + 10.0);
    $this->assertSame(6, \ord($png[25]), 'PNG color type RGBA; Pocket Code reads grayscale as alpha');
  }

  public function testAnAbsurdRotationCenterDoesNotAllocateAHugeCanvas(): void
  {
    $pixel = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', true);
    $png = (new AssetNormalizer())->costumeToPng($pixel, ['dataFormat' => 'png', 'rotationCenterX' => 50000, 'rotationCenterY' => 50000], microtime(true) + 10.0);
    $size = getimagesizefromstring($png);
    $this->assertLessThanOrEqual(4096, $size[0] ?? PHP_INT_MAX);
    $this->assertLessThanOrEqual(4096, $size[1] ?? PHP_INT_MAX);
  }

  public function testABlockThatContainsItselfIsRejected(): void
  {
    $this->expectException(ScratchConversionException::class);
    self::convertBlocks([
      'hat' => ['opcode' => 'event_whenflagclicked', 'next' => 'loop', 'topLevel' => true, 'inputs' => [], 'fields' => []],
      'loop' => ['opcode' => 'control_forever', 'next' => null, 'inputs' => ['SUBSTACK' => [2, 'loop']], 'fields' => []],
    ]);
  }

  public function testTextStaysTextAndDoubleNotIsBracketed(): void
  {
    $xml = self::convertBlocks([
      'hat' => ['opcode' => 'event_whenflagclicked', 'next' => 'say', 'topLevel' => true, 'inputs' => [], 'fields' => []],
      'say' => ['opcode' => 'looks_say', 'next' => 'if', 'inputs' => ['MESSAGE' => [1, [10, '001']]], 'fields' => []],
      'if' => ['opcode' => 'control_if', 'next' => null, 'inputs' => ['CONDITION' => [2, 'not1']], 'fields' => []],
      'not1' => ['opcode' => 'operator_not', 'inputs' => ['OPERAND' => [2, 'not2']], 'fields' => []],
      'not2' => ['opcode' => 'operator_not', 'inputs' => [], 'fields' => []],
    ]);
    $xpath = new \DOMXPath($xml);
    $this->assertSame('STRING', $xpath->evaluate('string(//brick[@type="SayBubbleBrick"]//formula/type)'));
    $this->assertSame('001', $xpath->evaluate('string(//brick[@type="SayBubbleBrick"]//formula/value)'));
    $this->assertSame(1.0, $xpath->evaluate('count(//formula[value="LOGICAL_NOT"]/rightChild[type="BRACKET"]/rightChild[value="LOGICAL_NOT"])'));
  }

  /** @param array<string, mixed> $last */
  #[DataProvider('explodingSharedReporters')]
  public function testSharedReportersThatWouldExplodeAreRejectedQuicklyAndCheaply(string $opcode, int $levels, array $last): void
  {
    $blocks = ['hat' => ['opcode' => 'event_whenflagclicked', 'next' => 'say', 'topLevel' => true, 'inputs' => [], 'fields' => []]];
    $blocks['say'] = ['opcode' => 'looks_say', 'next' => null, 'inputs' => ['MESSAGE' => [3, 'r0', [10, '']]], 'fields' => []];
    [$a, $b] = 'operator_join' === $opcode ? ['STRING1', 'STRING2'] : ['NUM1', 'NUM2'];
    for ($i = 0; $i < $levels; ++$i) {
      $blocks['r'.$i] = ['opcode' => $opcode, 'inputs' => [$a => [3, 'r'.($i + 1), [10, '']], $b => [3, 'r'.($i + 1), [10, '']]], 'fields' => []];
    }

    $blocks['r'.$levels] = $last;

    memory_reset_peak_usage();
    $memory = memory_get_usage();
    $start = microtime(true);
    try {
      self::convertBlocks($blocks);
      $this->fail('The exploding project was converted');
    } catch (ScratchConversionException) {
    }

    $this->assertLessThan(5.0, microtime(true) - $start);
    $this->assertLessThan(64 * 1024 * 1024, memory_get_peak_usage() - $memory);
  }

  /** @return iterable<string, array{string, int, array<string, mixed>}> */
  public static function explodingSharedReporters(): iterable
  {
    // 40 blocks, each using the next one twice: 2^40 formula nodes if every use were converted in full.
    yield 'nodes' => ['operator_add', 40, ['opcode' => 'motion_xposition', 'inputs' => [], 'fields' => []]];
    // 15 blocks and one 8 KiB string: few nodes, but 256 MiB of text.
    yield 'text' => ['operator_join', 15, ['opcode' => 'operator_join', 'inputs' => ['STRING1' => [1, [10, str_repeat('x', 8192)]]], 'fields' => []]];
  }

  public function testAScriptThatRunsIntoItselfIsRejected(): void
  {
    $this->expectException(ScratchConversionException::class);
    self::convertBlocks([
      'hat' => ['opcode' => 'event_whenflagclicked', 'next' => 'a', 'topLevel' => true, 'inputs' => [], 'fields' => []],
      'a' => ['opcode' => 'motion_ifonedgebounce', 'next' => 'b', 'inputs' => [], 'fields' => []],
      'b' => ['opcode' => 'motion_ifonedgebounce', 'next' => 'a', 'inputs' => [], 'fields' => []],
    ]);
  }

  public function testChangeVariableCountsNonNumericTextAsZeroAndNotIsBracketedUnderPlus(): void
  {
    $xml = self::convertBlocks([
      'hat' => ['opcode' => 'event_whenflagclicked', 'next' => 'change', 'topLevel' => true, 'inputs' => [], 'fields' => []],
      'change' => ['opcode' => 'data_changevariableby', 'next' => 'say', 'inputs' => ['VALUE' => [1, [4, '1']]], 'fields' => ['VARIABLE' => ['score', 'v1']]],
      'say' => ['opcode' => 'looks_say', 'next' => null, 'inputs' => ['MESSAGE' => [3, 'add', [10, '']]], 'fields' => []],
      'add' => ['opcode' => 'operator_add', 'inputs' => ['NUM1' => [3, 'not', [4, '']], 'NUM2' => [1, [4, '1']]], 'fields' => []],
      'not' => ['opcode' => 'operator_not', 'inputs' => [], 'fields' => []],
    ], ['variables' => ['v1' => ['score', '0']]]);
    $xpath = new \DOMXPath($xml);
    $set = '//script[receivedMessage="__scratch green flag"]//brick[@type="SetVariableBrick"]';
    $this->assertSame(0.0, $xpath->evaluate('count(//brick[@type="ChangeVariableBrick"])'));
    $this->assertSame('PLUS', $xpath->evaluate('string('.$set.'/formulaList/formula/value)'));
    $current = $set.'/formulaList/formula/leftChild[type="FUNCTION" and value="IF_THEN_ELSE"]';
    $this->assertSame('score', $xpath->evaluate('string('.$current.'/rightChild[type="USER_VARIABLE"]/value)'));
    $this->assertSame('0', $xpath->evaluate('string('.$current.'/additionalChildren/*[type="NUMBER"]/value)'));
    $is_number = $current.'/leftChild[value="EQUAL"][leftChild[type="USER_VARIABLE" and value="score"]]';
    $this->assertSame(1.0, $xpath->evaluate('count('.$is_number.'/rightChild[value="PLUS"][leftChild[value="score"]][rightChild[value="0"]])'));
    $this->assertSame(1.0, $xpath->evaluate('count(//formula[value="PLUS"]/leftChild[type="BRACKET"]/rightChild[value="LOGICAL_NOT"])'));
  }

  public function testKeysBecomeOnScreenButtons(): void
  {
    $xml = self::convertBlocks([
      'hat' => ['opcode' => 'event_whenkeypressed', 'next' => null, 'topLevel' => true, 'inputs' => [], 'fields' => ['KEY_OPTION' => ['right arrow', null]]],
      'flag' => ['opcode' => 'event_whenflagclicked', 'next' => 'wait', 'topLevel' => true, 'inputs' => [], 'fields' => []],
      'wait' => ['opcode' => 'control_wait_until', 'next' => null, 'inputs' => ['CONDITION' => [2, 'any']], 'fields' => []],
      'any' => ['opcode' => 'sensing_keypressed', 'inputs' => ['KEY_OPTION' => [1, 'menu']], 'fields' => []],
      'menu' => ['opcode' => 'sensing_keyoptions', 'shadow' => true, 'inputs' => [], 'fields' => ['KEY_OPTION' => ['any', null]]],
    ]);
    $xpath = new \DOMXPath($xml);
    $held = '__scratch key right arrow';
    $this->assertSame(1.0, $xpath->evaluate('count(//script[@type="BroadcastScript"][receivedMessage="'.$held.'"])'));
    $this->assertSame($held, $xpath->evaluate('string(//brick[@type="WaitUntilBrick"]/formulaList/formula[@category="IF_CONDITION"][type="USER_VARIABLE"]/value)'));
    $this->assertSame(1.0, $xpath->evaluate('count(//programVariableList/userVariable)'));
    $button = '//object[@name="⌨ right arrow"]';
    $this->assertSame(1.0, $xpath->evaluate('count('.$button.'/lookList/look)'));
    $this->assertSame(1.0, $xpath->evaluate('count('.$button.'//brick[@type="SetVariableBrick"][formulaList/formula/value="COLLIDES_WITH_FINGER"])'));
    $this->assertSame(1.0, $xpath->evaluate('count('.$button.'//script[@type="WhenScript"]/brickList/brick[@type="BroadcastBrick"][broadcastMessage="'.$held.'"])'));
    $this->assertSame(1.0, $xpath->evaluate('count('.$button.'//brick[@type="RepeatUntilBrick"]//brick[@type="BroadcastBrick"][broadcastMessage="'.$held.'"])'));
  }

  public function testWasdKeysGetTheirOwnButtonsWithoutArrows(): void
  {
    $hat = static fn (string $key): array => ['opcode' => 'event_whenkeypressed', 'next' => null, 'topLevel' => true, 'inputs' => [], 'fields' => ['KEY_OPTION' => [$key, null]]];
    $xml = self::convertBlocks(['w' => $hat('w'), 'zero' => $hat('0'), 'up' => $hat('up arrow'), 's' => $hat('s')], ['name' => '⌨ s']);
    $xpath = new \DOMXPath($xml);
    $this->assertSame(1.0, $xpath->evaluate('count(//object[@name="⌨ 0"])'));
    $this->assertSame(1.0, $xpath->evaluate('count(//object[@name="⌨ up arrow"]//script[@type="WhenScript"]//broadcastMessage[.="__scratch key w"])'));
    $this->assertSame(0.0, $xpath->evaluate('count(//object[@name="⌨ w"])'));
    $this->assertSame(1.0, $xpath->evaluate('count(//object[@name="⌨ s 2"])')); // the project's own "⌨ s" keeps its name
  }

  public function testSwitchingToAMissingNameNeedsNoNameList(): void
  {
    $switch = static fn (string $opcode, string $input, string $menu, string $value, ?string $next): array => ['opcode' => $opcode, 'next' => $next, 'inputs' => [$input => [1, $menu]], 'fields' => []];
    $xml = self::convertBlocks([
      'hat' => ['opcode' => 'event_whenflagclicked', 'next' => 'a', 'topLevel' => true, 'inputs' => [], 'fields' => []],
      'a' => $switch('looks_switchbackdropto', 'BACKDROP', 'am', '', 'b'),
      'am' => ['opcode' => 'looks_backdrops', 'shadow' => true, 'inputs' => [], 'fields' => ['BACKDROP' => ['TITLE SCREEN', null]]],
      'b' => $switch('looks_switchcostumeto', 'COSTUME', 'bm', '', null),
      'bm' => ['opcode' => 'looks_costume', 'shadow' => true, 'inputs' => [], 'fields' => ['COSTUME' => ['2', null]]],
    ]);
    $xpath = new \DOMXPath($xml);
    $this->assertSame(0.0, $xpath->evaluate('count(//*[type="FUNCTION" and value="INDEX_OF_ITEM"])'));
    $this->assertSame('OBJECT_BACKGROUND_NUMBER', $xpath->evaluate('string(//brick[@type="SetBackgroundByIndexBrick"]/formulaList/formula/value)'));
    $this->assertSame(1.0, $xpath->evaluate('count(//brick[@type="SetLookByIndexBrick"])'));
  }

  public function testBroadcastsMatchIgnoringCase(): void
  {
    $xml = self::convertBlocks([
      'hat' => ['opcode' => 'event_whenflagclicked', 'next' => 'send', 'topLevel' => true, 'inputs' => [], 'fields' => []],
      'send' => ['opcode' => 'event_broadcast', 'next' => null, 'inputs' => ['BROADCAST_INPUT' => [1, [11, 'start project', 'b1']]], 'fields' => []],
      'get' => ['opcode' => 'event_whenbroadcastreceived', 'next' => null, 'topLevel' => true, 'inputs' => [], 'fields' => ['BROADCAST_OPTION' => ['Start Project', 'b1']]],
    ]);
    $xpath = new \DOMXPath($xml);
    $received = $xpath->evaluate('string(//script[@type="BroadcastScript"][brickList[not(*)]]/receivedMessage)');
    $this->assertSame('start project', strtolower($received));
    $this->assertSame(1.0, $xpath->evaluate('count(//brick[@type="BroadcastBrick"][broadcastMessage="'.$received.'"])'));
  }

  public function testTouchingTheMouseRemembersTheLastTouch(): void
  {
    $xml = self::convertBlocks([
      'hat' => ['opcode' => 'event_whenflagclicked', 'next' => 'wait', 'topLevel' => true, 'inputs' => [], 'fields' => []],
      'wait' => ['opcode' => 'control_wait_until', 'next' => null, 'inputs' => ['CONDITION' => [2, 'touch']], 'fields' => []],
      'touch' => ['opcode' => 'sensing_touchingobject', 'inputs' => ['TOUCHINGOBJECTMENU' => [1, 'menu']], 'fields' => []],
      'menu' => ['opcode' => 'sensing_touchingobjectmenu', 'shadow' => true, 'inputs' => [], 'fields' => ['TOUCHINGOBJECTMENU' => ['_mouse_', null]]],
    ]);
    $xpath = new \DOMXPath($xml);
    $condition = '//brick[@type="WaitUntilBrick"]/formulaList/formula[value="LOGICAL_OR"]';
    $this->assertSame('COLLIDES_WITH_FINGER', $xpath->evaluate('string('.$condition.'/leftChild/value)'));
    $this->assertSame('__scratch finger on me', $xpath->evaluate('string('.$condition.'/rightChild[value="EQUAL"]/leftChild[type="USER_VARIABLE"]/value)'));
    // Updated only while the screen is touched, by the sprite and by each of its clones.
    $remember = '//brick[@type="IfThenLogicBeginBrick"][formulaList/formula/value="FINGER_TOUCHED"]//brick[@type="SetVariableBrick"][formulaList/formula[value="IF_THEN_ELSE"]/leftChild/value="COLLIDES_WITH_FINGER"]';
    $this->assertSame(1.0, $xpath->evaluate('count(//script[@type="StartScript"]'.$remember.')'));
    $this->assertSame(1.0, $xpath->evaluate('count(//script[@type="WhenClonedScript"]'.$remember.')'));
  }

  public function testMusicBlocksBecomeMusicBricks(): void
  {
    $menu = static fn (string $opcode, string $field, string $value): array => ['opcode' => $opcode, 'shadow' => true, 'inputs' => [], 'fields' => [$field => [$value, null]]];
    $xml = self::convertBlocks([
      'hat' => ['opcode' => 'event_whenflagclicked', 'next' => 'inst', 'topLevel' => true, 'inputs' => [], 'fields' => []],
      'inst' => ['opcode' => 'music_setInstrument', 'next' => 'note', 'inputs' => ['INSTRUMENT' => [1, 'im']], 'fields' => []],
      'im' => $menu('music_menu_INSTRUMENT', 'INSTRUMENT', '3'),
      'note' => ['opcode' => 'music_playNoteForBeats', 'next' => 'drum', 'inputs' => ['NOTE' => [1, 'nm'], 'BEATS' => [1, [4, '0.5']]], 'fields' => []],
      'nm' => $menu('note', 'NOTE', '60'),
      'drum' => ['opcode' => 'music_playDrumForBeats', 'next' => null, 'inputs' => ['DRUM' => [1, 'dm'], 'BEATS' => [1, [4, '0.25']]], 'fields' => []],
      'dm' => $menu('music_menu_DRUM', 'DRUM', '18'),
    ]);
    $xpath = new \DOMXPath($xml);
    $this->assertSame('ORGAN', $xpath->evaluate('string(//brick[@type="SetInstrumentBrick"]/instrumentSelection)'));
    $this->assertSame('60', $xpath->evaluate('string(//brick[@type="PlayNoteForBeatsBrick"]/formulaList/formula[@category="NOTE_TO_PLAY"]/value)'));
    $this->assertSame('OPEN_CUICA', $xpath->evaluate('string(//brick[@type="PlayDrumForBeatsBrick"]/drumSelection)'));
  }

  public function testAComputedInstrumentPicksFromTheMenu(): void
  {
    $xml = self::convertBlocks([
      'hat' => ['opcode' => 'event_whenflagclicked', 'next' => 'inst', 'topLevel' => true, 'inputs' => [], 'fields' => []],
      'inst' => ['opcode' => 'music_setInstrument', 'next' => null, 'inputs' => ['INSTRUMENT' => [3, [12, 'which', 'v1'], 'im']], 'fields' => []],
      'im' => ['opcode' => 'music_menu_INSTRUMENT', 'shadow' => true, 'inputs' => [], 'fields' => ['INSTRUMENT' => ['1', null]]],
    ], ['variables' => ['v1' => ['which', 3]]]);
    $xpath = new \DOMXPath($xml);
    $this->assertSame(21.0, $xpath->evaluate('count(//brick[@type="IfThenLogicBeginBrick"]/ifBranchBricks/brick[@type="SetInstrumentBrick"])'));
    $this->assertSame('ORGAN', $xpath->evaluate('string(//brick[@type="IfThenLogicBeginBrick"][formulaList/formula/rightChild/value="3"]//instrumentSelection)'));
  }

  public function testHelperNamesStayClearOfTheProjectsOwnNames(): void
  {
    $xml = self::convertBlocks([
      'hat' => ['opcode' => 'event_whenflagclicked', 'next' => 'ask', 'topLevel' => true, 'inputs' => [], 'fields' => []],
      'ask' => ['opcode' => 'sensing_askandwait', 'next' => 'send', 'inputs' => ['QUESTION' => [1, [10, '?']]], 'fields' => []],
      'send' => ['opcode' => 'event_broadcast', 'next' => null, 'inputs' => ['BROADCAST_INPUT' => [1, [11, '__scratch init sprites', 'b2']]], 'fields' => []],
      'own' => ['opcode' => 'event_whenbroadcastreceived', 'next' => null, 'topLevel' => true, 'inputs' => [], 'fields' => ['BROADCAST_OPTION' => ['__scratch GREEN FLAG', 'b1']]],
    ], ['variables' => ['v1' => ['__scratch answer', 0]], 'lists' => ['l1' => ['__scratch answer 2', []]]]);
    $xpath = new \DOMXPath($xml);
    $this->assertSame('__scratch green flag 2', $xpath->evaluate('string(//script[@type="StartScript"]//brick[@type="BroadcastBrick"]/broadcastMessage)'));
    $this->assertSame('__scratch init sprites 2', $xpath->evaluate('string(//script[@type="StartScript"]//brick[@type="BroadcastWaitBrick"]/broadcastMessage)'));
    $this->assertSame('__scratch answer 3', $xpath->evaluate('string(//brick[@type="AskBrick"]/userVariable//name)'));
    $this->assertSame(1.0, $xpath->evaluate('count(//script[receivedMessage="__scratch GREEN FLAG"])'));
  }

  public function testCostumeNumbersWrapInConstantTime(): void
  {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="2" height="2"/>';
    $costume = static fn (string $name): array => ['name' => $name, 'md5ext' => 'a.svg', 'assetId' => 'a', 'dataFormat' => 'svg'];
    $xml = self::convertBlocks([
      'hat' => ['opcode' => 'event_whenflagclicked', 'next' => 'switch', 'topLevel' => true, 'inputs' => [], 'fields' => []],
      'switch' => ['opcode' => 'looks_switchcostumeto', 'next' => null, 'inputs' => ['COSTUME' => [3, 'x', 'menu']], 'fields' => []],
      'menu' => ['opcode' => 'looks_costume', 'shadow' => true, 'inputs' => [], 'fields' => ['COSTUME' => ['a', null]]],
      'x' => ['opcode' => 'data_variable', 'inputs' => [], 'fields' => ['VARIABLE' => ['x', 'v1']]],
    ], ['variables' => ['v1' => ['x', 0]], 'costumes' => [$costume('a'), $costume('b'), $costume('c')]], ['a.svg' => $svg]);
    $xpath = new \DOMXPath($xml);
    $this->assertSame(0.0, $xpath->evaluate('count(//*[type="FUNCTION" and value="MOD"])'));
    $wrapped = ($xpath->query('//brick[@type="SetLookByIndexBrick"]/formulaList/formula/additionalChildren/*') ?: null)?->item(0);
    $this->assertInstanceOf(\DOMElement::class, $wrapped);

    // Scratch: costume 0 of 3 is costume 3, -1 is 2, 4 is 1.
    foreach ([[0.0, 3.0], [-1.0, 2.0], [4.0, 1.0], [2.6, 3.0]] as [$x, $expected]) {
      $this->assertSame($expected, self::evaluate($wrapped, $x));
    }

    // Huge values lose precision in doubles, but there is no loop left to stall on.
    foreach ([-1e20, 1e20, -PHP_FLOAT_MAX] as $x) {
      $this->assertTrue(is_finite(self::evaluate($wrapped, $x)));
    }
  }

  /** Evaluates a formula like Catroid's FormulaElement does, for the few element types the costume wrap uses. */
  private static function evaluate(\DOMElement $element, float $x): float
  {
    $child = static fn (string $name): ?\DOMElement => self::child($element, $name);
    $value = (string) $child('value')?->textContent;
    $left_child = $child('leftChild');
    $right_child = $child('rightChild');
    $left = null === $left_child ? null : self::evaluate($left_child, $x);
    $right = null === $right_child ? 0.0 : self::evaluate($right_child, $x);

    return match ((string) $child('type')?->textContent.' '.$value) {
      'NUMBER '.$value => (float) $value,
      'USER_VARIABLE x' => $x,
      'BRACKET ' => $right,
      'OPERATOR PLUS' => $left + $right,
      'OPERATOR MINUS' => null === $left ? -$right : $left - $right,
      'OPERATOR MULT' => $left * $right,
      'OPERATOR DIVIDE' => $left / $right,
      'FUNCTION ROUND' => (float) max(PHP_INT_MIN, min(PHP_INT_MAX, floor((float) $left + 0.5))), // Java's Math.round to long
      'FUNCTION FLOOR' => floor((float) $left),
      default => throw new \LogicException('Unexpected formula element '.$value),
    };
  }

  /**
   * @param array<string, array<string, mixed>> $blocks blocks of a single sprite
   * @param array<string, mixed>                $sprite overrides for the sprite target
   * @param array<string, string>               $assets md5ext => bytes
   */
  private static function convertBlocks(array $blocks, array $sprite = [], array $assets = []): \DOMDocument
  {
    $project = ['targets' => [
      ['isStage' => true, 'name' => 'Stage', 'variables' => [], 'lists' => [], 'blocks' => [], 'costumes' => [], 'sounds' => []],
      [...['isStage' => false, 'name' => 'Sprite', 'variables' => [], 'lists' => [], 'blocks' => $blocks, 'costumes' => [], 'sounds' => []], ...$sprite],
    ]];
    $archive = (string) tempnam(sys_get_temp_dir(), 'scratch-test');
    (new ScratchToCatrobatConverter(new AssetNormalizer()))->convert(1, $project, ['title' => 'Blocks'], $assets, null, $archive);
    $zip = new \ZipArchive();
    $zip->open($archive);
    $xml = new \DOMDocument();
    $xml->loadXML((string) $zip->getFromName('code.xml') ?: '<program/>');

    return $xml;
  }

  private static function child(\DOMElement $parent, string $step): ?\DOMElement
  {
    if (1 !== preg_match('/^([^\[]+)(?:\[(\d+)])?$/', $step, $match)) {
      return null;
    }

    $index = (int) ($match[2] ?? 1);
    foreach ($parent->childNodes as $child) {
      if ($child instanceof \DOMElement && $child->tagName === $match[1] && 0 === --$index) {
        return $child;
      }
    }

    return null;
  }
}
