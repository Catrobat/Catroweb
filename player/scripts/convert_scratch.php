<?php

declare(strict_types=1);

/*
 * Converts every Scratch fixture (fixtures/scratch/<name>/: project.json, its costumes and sounds, credit.json) with
 * share's own converter into <out dir>/<name>.catrobat, as an import on share would, and prints the Scratch blocks it
 * could not convert. scratch_check.sh then loads and plays the results. Needs Catroweb's vendor/ (composer install)
 * and rsvg-convert + sox, like the app. Usage: php convert_scratch.php <out dir>
 */

use App\Project\Scratch\Converter\AssetNormalizer;
use App\Project\Scratch\Converter\ScratchToCatrobatConverter;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$out = $argv[1] ?? throw new InvalidArgumentException('Usage: php convert_scratch.php <out dir>');
@mkdir($out, 0o777, true);
$converter = new ScratchToCatrobatConverter(new AssetNormalizer());
$failed = 0;
$dirs = glob(dirname(__DIR__).'/fixtures/scratch/*', GLOB_ONLYDIR) ?: throw new RuntimeException('No Scratch fixtures found.');
foreach ($dirs as $dir) {
  $name = basename($dir);
  $credit = json_decode((string) file_get_contents($dir.'/credit.json'), true, 512, JSON_THROW_ON_ERROR);
  $project = json_decode((string) file_get_contents($dir.'/project.json'), true, 512, JSON_THROW_ON_ERROR);
  $assets = [];
  foreach (glob($dir.'/*.{svg,png,jpg,gif,bmp,wav,mp3}', GLOB_BRACE) ?: [] as $file) {
    $assets[basename($file)] = (string) file_get_contents($file);
  }
  $metadata = ['title' => $credit['title'], 'author' => ['username' => $credit['author']], 'description' => ''];
  try {
    $unsupported = $converter->convert($credit['scratch_id'], $project, $metadata, $assets, null, "{$out}/{$name}.catrobat");
    ksort($unsupported);
    $list = implode(', ', array_map(static fn (string $op, int $n): string => "{$op} {$n}×", array_keys($unsupported), $unsupported));
    echo "{$name}: converted".('' === $list ? '' : ", unsupported: {$list}")."\n";
  } catch (Throwable $e) {
    echo "{$name}: FAILED: {$e->getMessage()}\n";
    ++$failed;
  }
}
exit($failed > 0 ? 1 : 0);
