<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\CatrobatCodeParser\CatrobatCodeParser;
use App\Catrobat\Services\ExtractedFileRepository;
use App\Entity\ProgramManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CodeViewController extends AbstractController
{
  public function viewCodeAction(string $id, ProgramManager $programManager, ExtractedFileRepository $extractedFileRepository,
                                 CatrobatCodeParser $catrobatCodeParser): Response
  {
    try
    {
      $program = $programManager->find($id);
      $extracted_program = $extractedFileRepository->loadProgramExtractedFile($program);
      if (null === $extracted_program)
      {
        throw new Exception();
      }
      $parsed_program = $catrobatCodeParser->parse($extracted_program);

      $web_path = $extracted_program->getWebPath();
    }
    catch (Exception $exception)
    {
      $parsed_program = null;
      $web_path = null;
    }

    $code_view_twig_params = [
      'parsed_program' => $parsed_program,
      'path' => $web_path,
    ];

    return $this->render('Program/codeview.html.twig', $code_view_twig_params);
  }
}
