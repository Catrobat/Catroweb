<?php

namespace App\Catrobat\Controller\Admin;

use App\Catrobat\Commands\CreateProgramExtensionsCommand;
use App\Catrobat\Services\ProgramFileRepository;
use App\Repository\ExtensionRepository;
use App\Repository\ProgramRepository;
use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * Class ExtensionController
 * @package App\Catrobat\Controller\Admin
 */
class ExtensionController extends Controller
{

  /**
   * @return RedirectResponse
   * @throws \Exception
   */
  public function extensionsAction(KernelInterface $kernel)
  {
    /**
     * @var $em EntityManager
     * @var $program_repositorysitory ProgramRepository
     */

    if (false === $this->admin->isGranted('EXTENSIONS'))
    {
      throw new AccessDeniedException();
    }

    $application = new Application($kernel);
    $application->setAutoExit(false);

    $input = new ArrayInput([
      'command' => 'catrobat:create:extensions',
    ]);

    $return = $application->run($input, new NullOutput());
    if ($return == 0)
    {
      $this->addFlash('sonata_flash_success', 'Creating extensions finished!');
    }
    else
    {
      $this->addFlash('sonata_flash_error', 'Creating extensions failed!');
    }

    return new RedirectResponse($this->admin->generateUrl("list"));
  }


  /**
   * @param Request|null $request
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function listAction(Request $request = null)
  {
    if (false === $this->admin->isGranted('LIST'))
    {
      throw new AccessDeniedException();
    }

    $url = $this->admin->generateUrl("extensions");

    return $this->renderWithExtraParams('Admin/extension.html.twig', ['url' => $url]);
  }
}
