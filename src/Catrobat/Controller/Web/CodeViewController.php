<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\ExtractedFileRepository;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Catrobat\Services\CatrobatCodeParser\CatrobatCodeParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * Class CodeViewController
 * @package App\Catrobat\Controller\Web
 */
class CodeViewController extends AbstractController
{

  /**
   * @param $id
   * @param ProgramManager $programManager
   * @param ExtractedFileRepository $extractedFileRepository
   * @param CatrobatCodeParser $catrobatCodeParser
   *
   * @return mixed
   */
  public function viewCodeAction($id, ProgramManager $programManager, ExtractedFileRepository $extractedFileRepository,
                                 CatrobatCodeParser $catrobatCodeParser)
  {
    /**
     * @var $program Program
     */
    try
    {
      $program = $programManager->find($id);
      $extracted_program = $extractedFileRepository->loadProgramExtractedFile($program);

      $parsed_program = $catrobatCodeParser->parse($extracted_program);

      $web_path = $extracted_program->getWebPath();
    } catch (\Exception $e)
    {
      $parsed_program = null;
      $web_path = null;
    }

    $code_view_twig_params = [
      'parsed_program' => $parsed_program,
      'path'           => $web_path,
    ];

    return $this->get('templating')->renderResponse('Program/codeview.html.twig', $code_view_twig_params);
  }
}