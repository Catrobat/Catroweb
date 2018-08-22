<?php

namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class CodeViewController extends Controller
{
  /**  ToDo?? no route?
   * , methods={"GET"}
   */
  public function viewCodeAction($id)
  {
    try
    {
      $program = $this->get('programmanager')->find($id);
      $extracted_program = $this->get('extractedfilerepository')->loadProgramExtractedFile($program);

      $parsed_program = $this->get('catrobat_code_parser')->parse($extracted_program);
      $web_path = $extracted_program->getWebPath();
    }
    catch(\Exception $e)
    {
      $parsed_program = null;
      $web_path = null;
    }

    $code_view_twig_params = array(
      'parsed_program' => $parsed_program,
      'path' => $web_path
    );

    return $this->get('templating')->renderResponse('::codeview.html.twig', $code_view_twig_params);
  }
}