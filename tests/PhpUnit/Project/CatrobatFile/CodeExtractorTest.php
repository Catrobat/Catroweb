<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatCode\CodeObject;
use App\Project\CatrobatCode\SyntaxHighlightingConstants;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class CodeExtractorTest extends TestCase
{
  public function testXmlWithIfAndCondition(): void
  {
    $this->checkFiles('tests/TestData/DataFixtures/CodeXmls/ifWithCondition/');
  }

  public function testXmlWithFormula(): void
  {
    $this->checkFiles('tests/TestData/DataFixtures/CodeXmls/formula/');
  }

  public function testXmlWithIf(): void
  {
    $this->checkFiles('tests/TestData/DataFixtures/CodeXmls/if/');
  }

  public function testXmlWithLoop(): void
  {
    $this->checkFiles('tests/TestData/DataFixtures/CodeXmls/loop/');
  }

  public function testXmlWithAllBricks(): void
  {
    $this->checkFiles('tests/TestData/DataFixtures/CodeXmls/allBricks/');
  }

  public function testXmlWithNestedFormula(): void
  {
    $this->checkFiles('tests/TestData/DataFixtures/CodeXmls/nestedFormula/');
  }

  public function testXmlWithNestedObjects(): void
  {
    $this->checkFiles('tests/TestData/DataFixtures/CodeXmls/nestedObjects/');
  }

  private function checkFiles(string $path): void
  {
    $output = $this->getCode($path);
    $referenceOutput = $this->readReferenceOutputFile($path);
    $this->assertEquals($referenceOutput, $output);
  }

  private function getCode(string $path): string
  {
    $eCFile = new ExtractedCatrobatFile($path, '', '');

    $objects = $eCFile->getCodeObjects();

    return $this->generateCode($objects);
  }

  /**
   * @param CodeObject[] $objects
   */
  private function generateCode(array $objects): string
  {
    $code = '';

    foreach ($objects as $object) {
      $code .= $object->getCode();
      $code .= $this->generateCode($object->getCodeObjects());
    }

    return $this->replaceSyntaxStrings($code);
  }

  private function replaceSyntaxStrings(string $code): string
  {
    $code = str_replace('&nbsp;', ' ', $code);
    $code = str_replace('<br/>', PHP_EOL, $code);
    $code = str_replace(SyntaxHighlightingConstants::LOOP, '', $code);
    $code = str_replace(SyntaxHighlightingConstants::END, '', $code);
    $code = str_replace(SyntaxHighlightingConstants::FUNCTIONS, '', $code);
    $code = str_replace(SyntaxHighlightingConstants::OBJECTS, '', $code);
    $code = str_replace(SyntaxHighlightingConstants::VALUE, '', $code);

    return str_replace(SyntaxHighlightingConstants::VARIABLES, '', $code);
  }

  private function readReferenceOutputFile(string $path): string
  {
    $absolutePath = $path.'reference.output';
    $referenceOutputFile = fopen($absolutePath, 'r');
    $size = filesize($absolutePath);

    if (!$referenceOutputFile || !$size) {
      exit('Unable to open file!');
    }

    $referenceOutput = fread($referenceOutputFile, $size);

    if (!$referenceOutput) {
      exit('Unable to redd file!');
    }

    fclose($referenceOutputFile);

    return $referenceOutput;
  }
}
