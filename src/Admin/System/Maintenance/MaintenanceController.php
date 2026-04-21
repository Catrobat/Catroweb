<?php

declare(strict_types=1);

namespace App\Admin\System\Maintenance;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @phpstan-extends CRUDController<object>
 */
class MaintenanceController extends CRUDController
{
  public function __construct(
    protected KernelInterface $kernel,
    #[Autowire('%catrobat.file.storage.dir%')]
    private readonly string $file_storage_dir,
    #[Autowire('%catrobat.logs.dir%')]
    private readonly string $log_dir,
    private readonly SystemHealthService $healthService,
  ) {
  }

  /**
   * @throws \Exception
   */
  public function compressedAction(): RedirectResponse
  {
    if (!$this->admin->isGranted('EXTRACTED')) {
      throw new AccessDeniedException();
    }

    $application = new Application($this->kernel);
    $application->setAutoExit(false);

    $input = new ArrayInput([
      'command' => 'catrobat:clean:compressed',
    ]);

    $return = $application->run($input, new NullOutput());
    if (0 === $return) {
      $this->addFlash('sonata_flash_success', 'Reset compressed files OK');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws \Exception
   */
  public function archiveLogsAction(): RedirectResponse
  {
    if (!$this->admin->isGranted('EXTRACTED')) {
      throw new AccessDeniedException();
    }

    $application = new Application($this->kernel);
    $application->setAutoExit(false);

    $input = new ArrayInput([
      'command' => 'catrobat:logs:archive',
    ]);

    $return = $application->run($input, new NullOutput());
    if (0 === $return) {
      $this->addFlash('sonata_flash_success', 'Archive log files OK');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws \Exception
   */
  public function deleteLogsAction(): RedirectResponse
  {
    if (!$this->admin->isGranted('EXTRACTED')) {
      throw new AccessDeniedException();
    }

    $application = new Application($this->kernel);
    $application->setAutoExit(false);

    $input = new ArrayInput([
      'command' => 'catrobat:clean:logs',
    ]);

    $output = new BufferedOutput();

    $return = $application->run($input, $output);

    if (0 === $return) {
      $this->addFlash('sonata_flash_success', 'Clean log files OK');
    } else {
      $message = "<strong>Failed cleaning log files:</strong><br />\n";
      $message .= str_replace("\n", "<br />\n", $output->fetch());
      $this->addFlash('sonata_flash_error', $message);
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  #[\Override]
  public function listAction(Request $request): Response
  {
    if (!$this->admin->isGranted('LIST')) {
      throw new AccessDeniedException();
    }

    // ... use any methods or services to get statistics data
    $RemovableObjects = [];
    $description = "This will remove all compressed catrobat files in the 'compressed'-directory and flag the projects accordingly";
    $rm = new RemovableMemory('Compressed Catrobatfiles', $description);
    $this->setSizeOfObject($rm, $this->file_storage_dir);
    $rm->setCommandName('Delete compressed files');
    $rm->setCommandLink($this->admin->generateUrl('compressed'));
    $RemovableObjects[] = $rm;
    $description = 'This will remove all log files.';
    $rm = new RemovableMemory('Logs', $description);
    $this->setSizeOfObject($rm, $this->log_dir);
    $rm->setCommandName('Delete log files');
    $rm->setCommandLink($this->admin->generateUrl('delete_logs'));
    $rm->setArchiveCommandLink($this->admin->generateUrl('archive_logs'));
    $rm->setArchiveCommandName('Archive logs files');
    $RemovableObjects[] = $rm;
    $disks = $this->getDiskStats();
    $primaryDisk = $disks[0];

    $usedSpaceRaw = $primaryDisk['used_raw'];
    foreach ($RemovableObjects as $obj) {
      $usedSpaceRaw -= $obj->size_raw;
    }

    $projectsSize = $this->get_dir_size($this->file_storage_dir);
    $usedSpaceRaw -= $projectsSize;
    $whole_ram = (float) shell_exec("free | grep Mem | awk '{print $2}'") * 1_000;
    $used_ram = (float) shell_exec("free | grep Mem | awk '{print $3}'") * 1_000;
    $free_ram = (float) shell_exec("free | grep Mem | awk '{print $4}'") * 1_000;
    $shared_ram = (float) shell_exec("free | grep Mem | awk '{print $5}'") * 1_000;
    $cached_ram = (float) shell_exec("free | grep Mem | awk '{print $6}'") * 1_000;
    $available_ram = (float) shell_exec("free | grep Mem | awk '{print $6}'") * 1_000;
    $free_ram_percentage = $whole_ram > 0 ? ($free_ram / $whole_ram) * 100 : 0;
    $used_ram_percentage = $whole_ram > 0 ? ($used_ram / $whole_ram) * 100 : 0;
    $shared_ram_percentage = $whole_ram > 0 ? ($shared_ram / $whole_ram) * 100 : 0;
    $cached_ram_percentage = $whole_ram > 0 ? ($cached_ram / $whole_ram) * 100 : 0;

    $storageDisk = $this->getStorageDisk($disks);

    return $this->render('Admin/SystemManagement/Maintain.html.twig', [
      'RemovableObjects' => $RemovableObjects,
      'disks' => $disks,
      'wholeSpace' => $this->getSymbolByQuantity($primaryDisk['total_raw']),
      'usedSpace' => $this->getSymbolByQuantity($usedSpaceRaw),
      'usedSpace_raw' => $usedSpaceRaw,
      'freeSpace_raw' => $primaryDisk['free_raw'],
      'freeSpace' => $this->getSymbolByQuantity($primaryDisk['free_raw']),
      'projectsSpace_raw' => $projectsSize,
      'projectsSpace' => $this->getSymbolByQuantity($projectsSize),
      'freeRamPercentage' => $free_ram_percentage,
      'usedRamPercentage' => $used_ram_percentage,
      'sharedRamPercentage' => $shared_ram_percentage,
      'cachedRamPercentage' => $cached_ram_percentage,
      'wholeRam' => $this->getSymbolByQuantity($whole_ram),
      'usedRam' => $this->getSymbolByQuantity($used_ram),
      'freeRam' => $this->getSymbolByQuantity($free_ram),
      'sharedRam' => $this->getSymbolByQuantity($shared_ram),
      'cachedRam' => $this->getSymbolByQuantity($cached_ram),
      'availableRam' => $this->getSymbolByQuantity($available_ram),
      'storagePressureLevel' => $this->healthService->getStoragePressureLevel(
        $storageDisk['used_percentage'], (int) $storageDisk['free_raw']
      ),
      'emailBudget' => $this->healthService->getEmailBudget(),
      'emailBudgetLevel' => $this->healthService->getEmailBudgetLevel(),
      'projectCounts' => $this->healthService->getProjectCounts(),
    ]);
  }

  /**
   * @return list<array{name: string, mount: string, total: string, used: string, free: string, total_raw: float, used_raw: float, free_raw: float, used_percentage: float}>
   */
  private function getDiskStats(): array
  {
    $mounts = ['/' => 'System', '/data' => 'Data', '/backup' => 'Backup'];
    $disks = [];

    foreach ($mounts as $path => $name) {
      $total = @disk_total_space($path);
      $free = @disk_free_space($path);
      if (false === $total || false === $free) {
        continue;
      }

      $used = max($total - $free, 0);
      $percentage = $total > 0 ? ($used / $total) * 100.0 : 0.0;

      $disks[] = [
        'name' => $name,
        'mount' => $path,
        'total' => $this->getSymbolByQuantity($total),
        'used' => $this->getSymbolByQuantity($used),
        'free' => $this->getSymbolByQuantity($free),
        'total_raw' => $total,
        'used_raw' => $used,
        'free_raw' => $free,
        'used_percentage' => $percentage,
      ];
    }

    return $disks;
  }

  /**
   * Returns the disk where project storage actually lives (follows symlinks).
   *
   * @param list<array{name: string, mount: string, total: string, used: string, free: string, total_raw: float, used_raw: float, free_raw: float, used_percentage: float}> $disks
   *
   * @return array{name: string, mount: string, total: string, used: string, free: string, total_raw: float, used_raw: float, free_raw: float, used_percentage: float}
   */
  private function getStorageDisk(array $disks): array
  {
    $storage_real = realpath($this->file_storage_dir) ?: $this->file_storage_dir;
    $best = $disks[0];
    $best_len = 0;

    foreach ($disks as $disk) {
      $mount = $disk['mount'];
      if (str_starts_with($storage_real, $mount) && strlen($mount) > $best_len) {
        $best = $disk;
        $best_len = strlen($mount);
      }
    }

    return $best;
  }

  private function get_dir_size(string $directory, ?array $extension = null): int
  {
    $count_size = 0;
    $count = 0;
    $dir_array = preg_grep('#^([^.])#', scandir($directory) ?: []); // no hidden files
    if (false === $dir_array) {
      return 0;
    }

    foreach ($dir_array as $filename) {
      if (null !== $extension && !in_array(pathinfo((string) $filename, PATHINFO_EXTENSION), $extension, true)) {
        continue;
      }

      if ('..' == $filename) {
        continue;
      }
      if ('.' == $filename) {
        continue;
      }
      if (is_dir($directory.'/'.$filename)) {
        $new_folder_size = $this->get_dir_size($directory.'/'.$filename);
        $count_size += $new_folder_size;
      } elseif (is_file($directory.'/'.$filename)) {
        $count_size += filesize($directory.'/'.$filename);
        ++$count;
      }
    }

    return $count_size;
  }

  private function setSizeOfObject(RemovableMemory $object, string $path, ?array $extension = null): void
  {
    if (is_dir($path)) {
      $size = $this->get_dir_size($path, $extension);
      $object->setSizeRaw($size);
      $object->setSize($this->getSymbolByQuantity($size));
    }
  }

  private function getSymbolByQuantity(float $bytes): string
  {
    if ($bytes <= 0) {
      return '0.00 B';
    }

    $symbol = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
    $exp = intval(floor(log($bytes) / log(1_024)));
    $exp = max(0, min($exp, count($symbol) - 1));

    return sprintf('%.2f '.$symbol[$exp], $bytes / 1_024 ** $exp);
  }
}
