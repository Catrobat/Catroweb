<?php

namespace App\Catrobat\Controller\Admin;

use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExtensionController extends CRUDController
{
  /**
   * @throws Exception
   */
  public function extensionsAction(KernelInterface $kernel): RedirectResponse
  {
    if (!$this->admin->isGranted('EXTENSIONS'))
    {
      throw new AccessDeniedException();
    }

    $application = new Application($kernel);
    $application->setAutoExit(false);

    $input = new ArrayInput([
      'command' => 'catrobat:create:extensions',
    ]);

    $return = $application->run($input, new NullOutput());
    if (0 == $return)
    {
      $this->addFlash('sonata_flash_success', 'Creating extensions finished!');
    }
    else
    {
      $this->addFlash('sonata_flash_error', 'Creating extensions failed!');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
