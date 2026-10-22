<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\Scratch;

use App\Project\Scratch\Converter\AssetNormalizer;
use App\Project\Scratch\Converter\ScratchConversionException;
use App\Project\Scratch\Converter\ScratchToCatrobatConverter;
use PHPUnit\Framework\Attributes\CoversClass;
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

  /** @param array<string, array<string, mixed>> $blocks blocks of a single sprite */
  private static function convertBlocks(array $blocks): \DOMDocument
  {
    $project = ['targets' => [
      ['isStage' => true, 'name' => 'Stage', 'variables' => [], 'lists' => [], 'blocks' => [], 'costumes' => [], 'sounds' => []],
      ['isStage' => false, 'name' => 'Sprite', 'variables' => [], 'lists' => [], 'blocks' => $blocks, 'costumes' => [], 'sounds' => []],
    ]];
    $archive = (string) tempnam(sys_get_temp_dir(), 'scratch-test');
    (new ScratchToCatrobatConverter(new AssetNormalizer()))->convert(1, $project, ['title' => 'Blocks'], [], null, $archive);
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
