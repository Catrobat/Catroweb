<?php

namespace App\Catrobat\Controller\Admin;

use App\Catrobat\Commands\CleanApkCommand;
use App\Catrobat\Commands\CleanExtractedFileCommand;
use App\Catrobat\Commands\CleanBackupsCommand;
use App\Catrobat\Commands\CreateBackupCommand;
use App\Catrobat\Commands\CreateProgramExtensionsCommand;
use App\Repository\ProgramRepository;
use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;
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
  public function extensionsAction()
  {
    /**
     * @var $em EntityManager
     * @var $progrm_repo ProgramRepository
     */

    if (false === $this->admin->isGranted('EXTENSIONS'))
    {
      throw new AccessDeniedException();
    }

    $em = $this->getDoctrine()->getManager();
    $program_file_dir = $this->get('kernel')->getRootDir() . "/../public/resources/programs/";
    $program_repo = $this->container->get('programrepository');

    $command = new CreateProgramExtensionsCommand($em, $program_file_dir, $program_repo);
    $command->setContainer($this->container);

    $return = $command->run(new ArrayInput([]), new NullOutput());
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
