<?php

declare(strict_types=1);

namespace App\Admin\System\Maintenance;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
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

    return $this->render('Admin/SystemManagement/Maintain.html.twig', [
      'RemovableObjects' => $RemovableObjects,
      'emailBudget' => $emailBudget = $this->healthService->getEmailBudget(),
      'emailBudgetLevel' => $this->healthService->getEmailBudgetLevel($emailBudget['remaining']),
      'projectCounts' => $this->healthService->getProjectCounts(),
    ]);
  }

  private function get_dir_size(string $directory, ?array $extension = null): int
  {
    if (!is_dir($directory)) {
      return 0;
    }

    // Sum exact file sizes (not block sizes) using find + ls + awk (portable across GNU/BusyBox)
    // Exclude hidden files (matches legacy scandir behavior that skipped dotfiles)
    $findCmd = sprintf('find %s -type f -not -name %s', escapeshellarg($directory), escapeshellarg('.*'));
    if (null !== $extension) {
      $patterns = array_map(static fn (string $ext): string => '-name '.escapeshellarg("*.{$ext}"), $extension);
      $findCmd .= ' \( '.implode(' -o ', $patterns).' \)';
    }

    $result = @shell_exec($findCmd." -exec ls -ln {} + 2>/dev/null | awk '{s+=\$5} END {print s+0}'");

    return \is_string($result) ? (int) trim($result) : 0;
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
