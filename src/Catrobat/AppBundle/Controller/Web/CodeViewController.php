<?php

namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class CodeViewController extends Controller
{
  /**
   * @Method({"GET"})
   */
  public function viewCodeAction($id)
  {
    $program = $this->get('programmanager')->find($id);
    $extracted_program = $this->get('extractedfilerepository')->loadProgramExtractedFile($program);
    $parsed_program = $this->get('catrobat_code_parser')->parse($extracted_program);

    $code_view_twig_params = array(
      'parsed_program' => $parsed_program,
      'path' => $extracted_program->getWebPath()
    );

    return $this->get('templating')->renderResponse('::codeview.html.twig', $code_view_twig_params);
  }
}