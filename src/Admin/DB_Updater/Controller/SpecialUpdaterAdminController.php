<?php

namespace App\Admin\DB_Updater\Controller;

use App\System\Commands\Helpers\CommandHelper;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @phpstan-extends CRUDController<\stdClass>
 */
class SpecialUpdaterAdminController extends CRUDController
{
  public function __construct(
    protected KernelInterface $kernel
  ) {
  }

  public function listAction(?Request $request = null): Response
  {
    return $this->renderWithExtraParams('Admin/DB_Updater/admin_special_updater.html.twig', [
      'updateSpecialUrl' => $this->admin->generateUrl('update_special'),
    ]);
  }

  /**
   * @throws \Exception
   */
  public function updateSpecialAction(): RedirectResponse
  {
    if (!$this->admin->isGranted('UPDATE_SPECIAL')) {
      throw new AccessDeniedException();
    }

    $output = new BufferedOutput();
    $result = CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:special'], ['timeout' => 86400], '', $output, $this->kernel
    );

    if (0 === $result) {
      $this->addFlash('sonata_flash_success', 'Database has been successfully updated');
    } else {
      $this->addFlash('sonata_flash_error', "Updating database failed!\n".$output->fetch());
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
