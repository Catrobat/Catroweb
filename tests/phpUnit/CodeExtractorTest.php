<?php

namespace Tests\phpUnit;

use App\Catrobat\CatrobatCode\CodeObject;
use App\Catrobat\CatrobatCode\SyntaxHighlightingConstants;
use App\Catrobat\Services\ExtractedCatrobatFile;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CodeExtractorTest extends TestCase
{
  /**
   * @test
   */
  public function xmlWithIfAndCondition(): void
  {
    $this->checkFiles('/Resources/CodeXmls/ifWithCondition/');
  }

  /**
   * @test
   */
  public function xmlWithFormula(): void
  {
    $this->checkFiles('/Resources/CodeXmls/formula/');
  }

  /**
   * @test
   */
  public function xmlWithIf(): void
  {
    $this->checkFiles('/Resources/CodeXmls/if/');
  }

  /**
   * @test
   */
  public function xmlWithLoop(): void
  {
    $this->checkFiles('/Resources/CodeXmls/loop/');
  }

  /**
   * @test
   */
  public function xmlWithAllBricks(): void
  {
    $this->checkFiles('/Resources/CodeXmls/allBricks/');
  }

  /**
   * @test
   */
  public function xmlWithNestedFormula(): void
  {
    $this->checkFiles('/Resources/CodeXmls/nestedFormula/');
  }

  /**
   * @test
   */
  public function xmlWithNestedObjects(): void
  {
    $this->checkFiles('/Resources/CodeXmls/nestedObjects/');
  }

  private function checkFiles(string $path): void
  {
    $output = $this->getCode($path);
    $referenceOutput = $this->readReferenceOutputFile($path);
    $this->assertEquals($referenceOutput, $output);
  }

  private function getCode(string $path): string
  {
    $eCFile = new ExtractedCatrobatFile(__DIR__.$path, '', '');

    $objects = $eCFile->getCodeObjects();

    return $this->generateCode($objects);
  }

  /**
   * @param CodeObject[] $objects
   */
  private function generateCode(array $objects): string
  {
    $code = '';

    /** @var CodeObject $object */
    foreach ($objects as $object)
    {
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
    $absolutePath = __DIR__.$path.'reference.output';
    $referenceOutputFile = fopen($absolutePath, 'r');
    $size = filesize($absolutePath);

    if (!$referenceOutputFile || !$size)
    {
      exit('Unable to open file!');
    }

    $referenceOutput = fread($referenceOutputFile, $size);

    if (!$referenceOutput)
    {
      exit('Unable to redd file!');
    }

    fclose($referenceOutputFile);

    return $referenceOutput;
  }
}
