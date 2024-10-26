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
    #[Autowire('%catrobat.apk.dir%')]
    private readonly string $apk_dir,
    #[Autowire('%catrobat.logs.dir%')]
    private readonly string $log_dir,
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
    if (0 == $return) {
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
    if (0 == $return) {
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

  /**
   * @throws \Exception
   */
  public function apkAction(): RedirectResponse
  {
    if (!$this->admin->isGranted('APK')) {
      throw new AccessDeniedException();
    }

    $application = new Application($this->kernel);
    $application->setAutoExit(false);

    $input = new ArrayInput([
      'command' => 'catrobat:clean:apk',
    ]);

    $output = new NullOutput();

    $return = $application->run($input, $output);

    if (0 == $return) {
      $this->addFlash('sonata_flash_success', 'Reset APK Projects OK');
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
    $description = "This will remove all generated apk-files in the 'apk'-directory and flag the projects accordingly";
    $rm = new RemovableMemory('Generated APKs', $description);
    $this->setSizeOfObject($rm, $this->apk_dir, ['apk']);
    $rm->setCommandName('Delete APKs');
    $rm->setCommandLink($this->admin->generateUrl('apk'));
    $RemovableObjects[] = $rm;
    $description = 'This will remove all log files.';
    $rm = new RemovableMemory('Logs', $description);
    $this->setSizeOfObject($rm, $this->log_dir);
    $rm->setCommandName('Delete log files');
    $rm->setCommandLink($this->admin->generateUrl('delete_logs'));
    $rm->setArchiveCommandLink($this->admin->generateUrl('archive_logs'));
    $rm->setArchiveCommandName('Archive logs files');
    $RemovableObjects[] = $rm;
    $freeSpace = disk_free_space('/');
    $usedSpace = disk_total_space('/') - $freeSpace;
    $usedSpace = max($usedSpace, 0);

    $usedSpaceRaw = $usedSpace;
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
    $free_ram_percentage = ($free_ram / $whole_ram) * 100;
    $used_ram_percentage = ($used_ram / $whole_ram) * 100;
    $shared_ram_percentage = ($shared_ram / $whole_ram) * 100;
    $cached_ram_percentage = ($cached_ram / $whole_ram) * 100;

    return $this->render('Admin/SystemManagement/Maintain.html.twig', [
      'RemovableObjects' => $RemovableObjects,
      'wholeSpace' => $this->getSymbolByQuantity($usedSpace + $freeSpace),
      'usedSpace' => $this->getSymbolByQuantity($usedSpaceRaw),
      'usedSpace_raw' => $usedSpaceRaw,
      'freeSpace_raw' => $freeSpace,
      'freeSpace' => $this->getSymbolByQuantity($freeSpace),
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
    ]);
  }

  private function get_dir_size(string $directory, ?array $extension = null): int
  {
    $count_size = 0;
    $count = 0;
    $dir_array = preg_grep('#^([^.])#', scandir($directory)); // no hidden files
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
    $symbol = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
    $exp = floor(log($bytes) / log(1_024)) > 0 ? intval(floor(log($bytes) / log(1_024))) : 0;

    return sprintf('%.2f '.$symbol[$exp], $bytes / 1_024 ** $exp);
  }
}
