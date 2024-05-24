<?php

declare(strict_types=1);

namespace App\Admin\DB_Updater\Controller;

use App\DB\Entity\Project\Extension;
use App\System\Commands\Helpers\CommandHelper;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @phpstan-extends CRUDController<Extension>
 */
class FlavorsAdminController extends CRUDController
{
  public function __construct(
    protected KernelInterface $kernel
  ) {
  }

  public function listAction(Request $request): Response
  {
    return $this->renderWithExtraParams('Admin/DB_Updater/admin_flavors.html.twig', [
      'action' => 'update_flavors',
      'updateFlavorsUrl' => $this->admin->generateUrl('update_flavors'),
    ]);
  }

  /**
   * @throws \Exception
   */
  public function updateFlavorsAction(): RedirectResponse
  {
    if (!$this->admin->isGranted('TAGS')) {
      throw new AccessDeniedException();
    }

    $output = new BufferedOutput();
    $result = CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:flavors'], ['timeout' => 86400], '', $output, $this->kernel
    );

    if (0 === $result) {
      $this->addFlash('sonata_flash_success', 'Flavors have been successfully updated');
    } else {
      $this->addFlash('sonata_flash_error', "Updating flavors failed!\n".$output->fetch());
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
