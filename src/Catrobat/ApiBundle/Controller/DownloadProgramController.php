<?php

namespace Catrobat\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Catrobat\CoreBundle\Model\ProgramManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Catrobat\CoreBundle\Services\ProgramFileRepository;

class DownloadProgramController
{
  /**
   * @var \Catrobat\CoreBundle\Model\ProgramManager
   */
  private $program_manager;
  
  /**
   * @var ProgramFileRepository
   */
  private $file_repository;
  
  function __construct(ProgramManager $program_manager, ProgramFileRepository $file_repository)
  {
    $this->program_manager = $program_manager;
    $this->file_repository = $file_repository;
  }
  
  function downloadProgramAction(Request $request, $id)
  {
    $program = $this->program_manager->find($id);
    if (!$program)
    {
      throw new NotFoundHttpException();
    }
    if (!$program->isVisible())
    {
      throw new NotFoundHttpException();
    }
    
    $file = $this->file_repository->getProgramFile($id);
    if ($file->isFile())
    {
      return new BinaryFileResponse($file);
    }
    throw new NotFoundHttpException();
  }
}