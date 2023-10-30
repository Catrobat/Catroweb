<?php

namespace App\Application\Controller\Project;

use App\DB\Entity\Project\Program;
use App\Project\CatrobatCode\Parser\CatrobatCodeParser;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\ProgramManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CodeViewController extends AbstractController
{
  public function __construct(private readonly ProgramManager $program_manager, private readonly ExtractedFileRepository $extracted_file_repository, private readonly CatrobatCodeParser $code_parser, private readonly ParameterBagInterface $parameter_bag, private readonly TranslatorInterface $translator)
  {
  }

  #[Route(path: '/project/{id}/code_view', name: 'code_view', methods: ['GET'])]
  public function view(string $id): Response
  {
    /** @var Program|null $project */
    $project = $this->program_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $project) {
      $this->addFlash('snackbar', $this->translator->trans('snackbar.project_not_found', [], 'catroweb'));

      return $this->redirectToRoute('index');
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
    try {
      $program = $this->program_manager->find($id);
      $extracted_program = $this->extracted_file_repository->loadProgramExtractedFile($program);
      if (null === $extracted_program) {
        throw new \Exception();
      }
      $parsed_program = $this->code_parser->parse($extracted_program);

      $web_path = $extracted_program->getWebPath();
    } catch (\Exception) {
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
