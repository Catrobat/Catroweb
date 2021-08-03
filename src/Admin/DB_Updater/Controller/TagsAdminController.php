<?php

namespace App\Admin\DB_Updater\Controller;

use App\Commands\Helpers\CommandHelper;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TagsAdminController extends CRUDController
{
  public function listAction(Request $request = null): Response
  {
    return $this->renderWithExtraParams('Admin/DB_Updater/admin_tags.html.twig', [
      'action' => 'update_tags',
      'updateTagsUrl' => $this->admin->generateUrl('update_tags'),
    ]);
  }

  /**
   * @throws Exception
   */
  public function updateTagsAction(KernelInterface $kernel): RedirectResponse
  {
    if (!$this->admin->isGranted('TAGS')) {
      throw new AccessDeniedException();
    }

    $output = new BufferedOutput();
    $result = CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:tags'], ['timeout' => 86400], '', $output, $kernel
    );

    if (0 === $result) {
      $this->addFlash('sonata_flash_success', 'Tags have been successfully updated');
    } else {
      $this->addFlash('sonata_flash_error', "Updating tags failed!\n".$output->fetch());
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
