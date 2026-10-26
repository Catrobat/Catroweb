<?php

declare(strict_types=1);

namespace App\Project\CodeView;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Runs the browser player's code export (player/README.md, "Code view export") under Node: Catroid's own loader and
 * bricks read a code.xml and describe each brick as the app shows it. code.xml is untrusted, so Node runs with its
 * permission model (it may read only the export and that one file; no writes, child processes or workers) and with
 * an empty environment, so it has none of share's secrets. Node 22's permission model does not restrict the network.
 */
class CodeExportRunner
{
  private const int TIMEOUT_SECONDS = 60;

  private const int MEMORY_MB = 1024;

  /** GNU coreutils' timeout (servers, CI; not macOS). */
  private const string TIMEOUT = '/usr/bin/timeout';

  public function __construct(
    #[Autowire('%catrobat.code_export.dir%')]
    private readonly string $export_dir,
    private readonly LoggerInterface $logger,
  ) {
  }

  /**
   * Changes when what the export is built from changes (its version.txt), so exports of an older one are made again.
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
   * The code view of this code.xml, as the export writes it (CodeExport in the player), or ['error' => why] when the
   * export ran and says the project cannot be shown (e.g. its code.xml is broken): the same every time.
   *
   * @return array<string, mixed>
   *
   * @throws CodeExportUnavailableException when the export could not run or did not finish (may work later)
   */
  public function export(string $code_xml_path): array
  {
    $script = realpath($this->export_dir.'/export.mjs');
    $xml = realpath($code_xml_path);
    $node = (new ExecutableFinder())->find('node', null, ['/usr/local/bin', '/usr/bin']);
    if (false === $script || false === $xml || null === $node) {
      throw new CodeExportUnavailableException('The code export is not available');
    }

    // timeout: Node ends even when PHP-FPM kills the worker that started it (its export slot is free then).
    // env -i: Symfony's Process passes PHP's environment (APP_SECRET, DATABASE_URL, ...) on to the child.
    $process = new Process([
      ...(is_executable(self::TIMEOUT) ? [self::TIMEOUT, '-k', '5', (string) (self::TIMEOUT_SECONDS + 5)] : []),
      '/usr/bin/env',
      '-i',
      $node,
      '--permission',
      '--allow-fs-read='.dirname($script),
      '--allow-fs-read='.$xml,
      '--max-old-space-size='.self::MEMORY_MB,
      $script,
      $xml,
    ], dirname($script));
    $process->setTimeout(self::TIMEOUT_SECONDS);

    try {
      $process->run();
    } catch (\Throwable $e) {
      $this->logger->warning('Code export did not finish: '.$e->getMessage(), ['file' => $code_xml_path]);

      throw new CodeExportUnavailableException('The code export did not finish', 0, $e);
    }

    if (!$process->isSuccessful()) {
      $this->logger->warning('Code export failed', ['file' => $code_xml_path, 'exit' => $process->getExitCode(), 'stderr' => mb_substr($process->getErrorOutput(), 0, 2000)]);

      throw new CodeExportUnavailableException('The code export failed');
    }

    try {
      $export = json_decode(trim($process->getOutput()), true, 100_000, JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
      throw new CodeExportUnavailableException('The code export wrote no JSON', 0, $e);
    }
    if (!is_array($export)) {
      throw new CodeExportUnavailableException('The code export wrote no JSON object');
    }

    return $export;
  }
}
