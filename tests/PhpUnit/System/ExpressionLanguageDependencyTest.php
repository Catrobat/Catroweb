<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/** @internal */
#[CoversNothing]
#[Group('unit')]
final class ExpressionLanguageDependencyTest extends TestCase
{
  #[DataProvider('expressionShapes')]
  public function testInstalledParserRejectsExcessiveTreesWithoutBreakingNormalCachedExpressions(string $shape): void
  {
    // Integration probe of the installed dependency, isolated from the PHPUnit process.
    // Never exercise advisory inputs against an unpatched installation.
    $script = <<<'PHP'
      require $argv[1];
      $version = Composer\InstalledVersions::getVersion('symfony/expression-language');
      if ($version === null || version_compare($version, '8.1.9', '<')) {
        fwrite(STDERR, "Patched ExpressionLanguage required\n");
        exit(2);
      }
      $language = new Symfony\Component\ExpressionLanguage\ExpressionLanguage(new Symfony\Component\Cache\Adapter\ArrayAdapter());
      for ($i = 0; $i < 2; ++$i) {
        if ($language->evaluate('value * 2 + 1', ['value' => 20]) !== 41) {
          exit(3);
        }
      }
      $expression = match ($argv[2]) {
        'unary' => str_repeat('not ', 300).'true',
        'array' => str_repeat('[', 300).'1'.str_repeat(']', 300),
        'binary-composition', 'property-composition' => 'value',
        'ternary' => str_repeat('true ? 1 : ', 300).'0',
        default => throw new LogicException('Unknown expression shape'),
      };
      if (in_array($argv[2], ['binary-composition', 'property-composition'], true)) {
        $suffix = $argv[2] === 'binary-composition' ? str_repeat(' + 1', 20) : str_repeat('.field', 20);
        for ($i = 0; $i < 20; ++$i) {
          $expression = '('.$expression.$suffix.')';
        }
      }
      foreach (['lint', 'parse'] as $operation) {
        try {
          $language->$operation($expression, ['value']);
          fwrite(STDERR, "Excessive tree accepted\n");
          exit(4);
        } catch (Symfony\Component\ExpressionLanguage\SyntaxError $exception) {
          if (!str_contains($exception->getMessage(), 'depth') && !str_contains($exception->getMessage(), 'nesting')) {
            fwrite(STDERR, $exception->getMessage());
            exit(5);
          }
        }
      }
      echo "normal-cache-ok; excessive-lint-and-parse-rejected\n";
      PHP;
    $process = new Process([PHP_BINARY, '-d', 'memory_limit=64M', '-r', $script, \dirname(__DIR__, 3).'/vendor/autoload.php', $shape]);
    // Xdebug counts PHP frames rather than expression nodes and can stop first.
    $process->setEnv(['XDEBUG_MODE' => 'off']);
    $process->setTimeout(10);
    $process->run();
    self::assertSame(0, $process->getExitCode(), $process->getErrorOutput());
    self::assertSame("normal-cache-ok; excessive-lint-and-parse-rejected\n", $process->getOutput());
  }

  /** @return iterable<string, array{string}> */
  public static function expressionShapes(): iterable
  {
    foreach (['unary', 'array', 'binary-composition', 'property-composition', 'ternary'] as $shape) {
      yield $shape => [$shape];
    }
  }
}
