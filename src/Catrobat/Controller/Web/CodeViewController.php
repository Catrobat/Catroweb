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
  private ProgramManager $program_manager;
  private ExtractedFileRepository $extracted_file_repository;
  private CatrobatCodeParser $code_parser;

  public function __construct(ProgramManager $program_manager,
                              ExtractedFileRepository $extracted_file_repository,
                              CatrobatCodeParser $code_parser)
  {
    $this->program_manager = $program_manager;
    $this->extracted_file_repository = $extracted_file_repository;
    $this->code_parser = $code_parser;
  }

  public function view(string $id, string $version): Response
  {
    return $this->render('Program/code_view.html.twig', [
      'id' => $id,
      'version' => $version,
    ]);
  }

  public function oldView(string $id, bool $visible = true): Response
  {
    try
    {
      $program = $this->program_manager->find($id);
      $extracted_program = $this->extracted_file_repository->loadProgramExtractedFile($program);
      if (null === $extracted_program)
      {
        throw new Exception();
      }
      $parsed_program = $this->code_parser->parse($extracted_program);

      $web_path = $extracted_program->getWebPath();
    }
    catch (Exception $exception)
    {
      $parsed_program = null;
      $web_path = null;
    }

    return $this->render('Program/old_code_view.html.twig', [
      'parsed_program' => $parsed_program,
      'path' => $web_path,
      'visible' => $visible,
    ]);
  }
}
