<?php

namespace App\Admin\Translation\Controller;

use App\Commands\Helpers\CommandHelper;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractMachineTranslationAdminController extends CRUDController
{
  public const TYPE_PROJECT = 'TYPE_PROJECT';
  public const TYPE_COMMENT = 'TYPE_COMMENT';

  private string $type;

  public function __construct(string $type)
  {
    $this->type = $type;
  }

  public function listAction(Request $request = null): Response
  {
    return $this->renderWithExtraParams('Admin/Translation/admin_machine_translation.html.twig', [
      'action' => 'list',
      'trimUrl' => $this->admin->generateUrl('trim'),
    ]);
  }

  public function trimAction(KernelInterface $kernel): Response
  {
    // TODO check permission

    $request = $this->getRequest();
    $days = $request->get('days');

    if (3 < strlen($days) || !ctype_digit($days) || 1 > $days) {
      $this->addFlash('sonata_flash_error', 'Days must be greater than 1');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    if (self::TYPE_PROJECT === $this->type) {
      $entity = '--only-project';
    } elseif (self::TYPE_COMMENT === $this->type) {
      $entity = '--only-comment';
    } else {
      $this->addFlash('sonata_flash_error', 'Invalid controller type');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    $result = CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:translation:trim-storage', '--older-than', $days, $entity],
      ['timeout' => 86400], '', null, $kernel
    );

    if (0 === $result) {
      $this->addFlash('sonata_flash_success', 'Command finished successfully');
    } else {
      $this->addFlash('sonata_flash_error', 'Error occurred running command!');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
