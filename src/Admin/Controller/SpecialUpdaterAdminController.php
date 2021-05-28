<?php

namespace App\Admin\Controller;

use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SpecialUpdaterAdminController extends CRUDController
{
  public function listAction(Request $request = null): Response
  {
    return $this->renderWithExtraParams('Admin/admin_special_updater.html.twig', [
      'updateSpecialUrl' => $this->admin->generateUrl('update_special'),
    ]);
  }

  /**
   * @throws Exception
   */
  public function updateSpecialAction(KernelInterface $kernel): RedirectResponse
  {
    if (!$this->admin->isGranted('UPDATE_SPECIAL')) {
      throw new AccessDeniedException();
    }

    $application = new Application($kernel);
    $application->setAutoExit(false);
    $result = $application->run(new ArrayInput(['command' => 'catrobat:update:special']), new NullOutput());

    if (0 === $result) {
      $this->addFlash('sonata_flash_success', 'Database has been successfully updated');
    } else {
      $this->addFlash('sonata_flash_error', 'Updating database failed!');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
