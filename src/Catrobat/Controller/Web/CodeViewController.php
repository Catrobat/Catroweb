<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\CatrobatCode\Parser\CatrobatCodeParser;
use App\Catrobat\Services\ExtractedFileRepository;
use App\Entity\Program;
use App\Entity\ProgramManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CodeViewController extends AbstractController
{
  private ProgramManager $program_manager;
  private ExtractedFileRepository $extracted_file_repository;
  private CatrobatCodeParser $code_parser;
  private ParameterBagInterface $parameter_bag;

  public function __construct(ProgramManager $program_manager,
                              ExtractedFileRepository $extracted_file_repository,
                              CatrobatCodeParser $code_parser, ParameterBagInterface $parameter_bag)
  {
    $this->program_manager = $program_manager;
    $this->extracted_file_repository = $extracted_file_repository;
    $this->code_parser = $code_parser;
    $this->parameter_bag = $parameter_bag;
  }

  /**
   * @Route("/project/{id}/code_view", name="code_view", methods={"GET"})
   */
  public function view(string $id): Response
  {
    /** @var Program $project */
    $project = $this->program_manager->find($id);

    if (!$this->program_manager->isProjectVisibleForCurrentUser($project))
    {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    $this->parameter_bag->get('catrobat.file.extract.path');

    return $this->render('Program/code_view.html.twig', [
      'id' => $id,
      'version' => $project->getLanguageVersion(),
      'extracted_path' => $this->parameter_bag->get('catrobat.file.extract.path'),
      'extracted_dir_hash' => $project->getId(),
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
