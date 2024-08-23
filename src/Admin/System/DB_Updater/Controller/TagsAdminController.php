<?php

declare(strict_types=1);

namespace App\Admin\System\DB_Updater\Controller;

use App\DB\Entity\Project\Tag;
use App\System\Commands\Helpers\CommandHelper;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @phpstan-extends CRUDController<Tag>
 */
class TagsAdminController extends CRUDController
{
  public function __construct(
    protected KernelInterface $kernel
  ) {
  }

  #[\Override]
  public function listAction(Request $request): Response
  {
    return $this->renderWithExtraParams('Admin/SystemManagement/DbUpdater/tags.html.twig', [
      'action' => 'update_tags',
      'updateTagsUrl' => $this->admin->generateUrl('update_tags'),
    ]);
  }

  /**
   * @throws \Exception
   */
  public function updateTagsAction(): RedirectResponse
  {
    if (!$this->admin->isGranted('TAGS')) {
      throw new AccessDeniedException();
    }

    $output = new BufferedOutput();
    $result = CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:tags'], ['timeout' => 86400], '', $output, $this->kernel
    );

    if (0 === $result) {
      $this->addFlash('sonata_flash_success', 'Tags have been successfully updated');
    } else {
      $this->addFlash('sonata_flash_error', "Updating tags failed!\n".$output->fetch());
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
