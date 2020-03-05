<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\CatrobatCodeParser\CatrobatCodeParser;
use App\Catrobat\Services\ExtractedFileRepository;
use App\Entity\Program;
use App\Entity\ProgramManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class CodeViewController.
 */
class CodeViewController extends AbstractController
{
  /**
   * @param $id
   *
   * @return mixed
   */
  public function viewCodeAction($id, ProgramManager $programManager, ExtractedFileRepository $extractedFileRepository,
                                 CatrobatCodeParser $catrobatCodeParser)
  {
    try
    {
      /** @var Program $program */
      $program = $programManager->find($id);
      $extracted_program = $extractedFileRepository->loadProgramExtractedFile($program);
      if (null === $extracted_program)
      {
        throw new \Exception();
      }
      $parsed_program = $catrobatCodeParser->parse($extracted_program);

      $web_path = $extracted_program->getWebPath();
    }
    catch (\Exception $e)
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
