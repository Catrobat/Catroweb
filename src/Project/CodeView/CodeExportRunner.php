<?php

declare(strict_types=1);

namespace App\Project\CodeView;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Runs the browser player's code export (player/README.md, "Code view") under Node: Catroid's own loader and bricks
 * read a code.xml and describe each brick as the app shows it. code.xml is untrusted, so Node runs with its
 * permission model: it may read only the export and that one file, and has no network, child processes or writes.
 */
class CodeExportRunner
{
  private const int TIMEOUT_SECONDS = 60;

  private const int MEMORY_MB = 1024;

  public function __construct(
    #[Autowire('%catrobat.code_export.dir%')]
    private readonly string $export_dir,
    private readonly LoggerInterface $logger,
  ) {
  }

  /**
   * Changes with every build of the export (its version.txt), so exports of an older one are made again.
   */
  public function getVersion(): ?string
  {
    $file = $this->export_dir.'/version.txt';
    if (!is_file($file)) {
      return null;
    }

    $version = trim((string) file_get_contents($file));

    return '' === $version ? null : $version;
  }

  public function getExportDir(): string
  {
    return $this->export_dir;
  }

  /**
   * The code view of this code.xml, as the export writes it (CodeExport in the player); ['error' => why] if there is
   * none.
   *
   * @return array<string, mixed>
   */
  public function export(string $code_xml_path): array
  {
    $script = realpath($this->export_dir.'/export.mjs');
    $xml = realpath($code_xml_path);
    $node = (new ExecutableFinder())->find('node', null, ['/usr/local/bin', '/usr/bin']);
    if (false === $script || false === $xml || null === $node) {
      return ['error' => 'The code export is not available'];
    }

    $process = new Process([
      $node,
      '--permission',
      '--allow-fs-read='.dirname($script),
      '--allow-fs-read='.$xml,
      '--max-old-space-size='.self::MEMORY_MB,
      $script,
      $xml,
    ], dirname($script), ['NODE_OPTIONS' => false]);
    $process->setTimeout(self::TIMEOUT_SECONDS);

    try {
      $process->run();
    } catch (\Throwable $e) {
      $this->logger->warning('Code export failed: '.$e->getMessage(), ['file' => $code_xml_path]);

      return ['error' => 'The code export did not finish'];
    }

    if (!$process->isSuccessful()) {
      $this->logger->warning('Code export failed', ['file' => $code_xml_path, 'stderr' => mb_substr($process->getErrorOutput(), 0, 2000)]);

      return ['error' => 'The code export failed'];
    }

    try {
      $export = json_decode(trim($process->getOutput()), true, 100_000, JSON_THROW_ON_ERROR);
    } catch (\JsonException) {
      return ['error' => 'The code export wrote no JSON'];
    }

    return is_array($export) ? $export : ['error' => 'The code export wrote no JSON object'];
  }
}
