<?php

namespace App\Application\Controller\Project;

use App\DB\Entity\Project\Program;
use App\Project\CatrobatCode\Parser\CatrobatCodeParser;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\ProgramManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CodeStatisticsController extends AbstractController
{
  public function __construct(private readonly ProgramManager $program_manager, private readonly ExtractedFileRepository $extracted_file_repository, private readonly CatrobatCodeParser $code_parser, private readonly TranslatorInterface $translator)
  {
  }

  #[Route(path: '/project/{id}/code_statistics', name: 'code_statistics', methods: ['GET'])]
  public function view(string $id): Response
  {
    // Todo: create useful data structures in CodeStatistic.php
    // Todo: add more statistic
    // Todo: better display of statistics -> E.g. Dr.Scratch
    $parsed_program = null;
    /** @var Program|null $program */
    $program = $this->program_manager->find($id);
    if (null !== $program) {
      $extracted_file = $this->extracted_file_repository->loadProgramExtractedFile($program);
      if (null !== $extracted_file) {
        $parsed_program = $this->code_parser->parse($extracted_file);
      }
    }
    if (null === $parsed_program) {
      return $this->render('Program/code_statistics.html.twig', [
        'id' => $id,
      ]);
    }
    $stats = $parsed_program->getCodeStatistic();
    $brick_stats = $stats->getBrickTypeStatistic();

    return $this->render('Program/code_statistics.html.twig', [
      'id' => $id,
      'data' => [
        'scenes' => $this->getMappedProjectStatistic('codeview.scenes', $stats->getSceneStatistic()),
        'scripts' => $this->getMappedProjectStatistic('codeview.scripts', $stats->getScriptStatistic()),
        'bricks' => $this->getMappedProjectStatistic('codeview.bricks', $stats->getBrickStatistic()),
        'objects' => $this->getMappedProjectStatistic('codeview.objects', $stats->getObjectStatistic()),
        'looks' => $this->getMappedProjectStatistic('codeview.looks', $stats->getLookStatistic()),
        'sounds' => $this->getMappedProjectStatistic('codeview.sounds', $stats->getSoundStatistic()),
        'globals' => $this->getMappedProjectStatistic('codeview.globalVariables', $stats->getGlobalVarStatistic()),
        'locals' => $this->getMappedProjectStatistic('codeview.localVariables', $stats->getLocalVarStatistic()),
      ],

      'brick_data' => [
        'event-brick' => $this->getMappedBricksStatistic(
          'codeStatistics.eventBricks',
          $brick_stats['eventBricks']['numTotal'],
          $brick_stats['eventBricks']['different']['numDifferent']
        ),
        'control-brick' => $this->getMappedBricksStatistic(
          'codeStatistics.controlBricks',
          $brick_stats['controlBricks']['numTotal'],
          $brick_stats['controlBricks']['different']['numDifferent']
        ),
        'motion-brick' => $this->getMappedBricksStatistic(
          'codeStatistics.motionBricks',
          $brick_stats['motionBricks']['numTotal'],
          $brick_stats['motionBricks']['different']['numDifferent']
        ),
        'sound-brick' => $this->getMappedBricksStatistic(
          'codeStatistics.soundBricks',
          $brick_stats['soundBricks']['numTotal'],
          $brick_stats['soundBricks']['different']['numDifferent']
        ),
        'look-brick' => $this->getMappedBricksStatistic(
          'codeStatistics.looksBricks',
          $brick_stats['looksBricks']['numTotal'],
          $brick_stats['looksBricks']['different']['numDifferent']
        ),
        'pen-brick' => $this->getMappedBricksStatistic(
          'codeStatistics.penBricks',
          $brick_stats['penBricks']['numTotal'],
          $brick_stats['penBricks']['different']['numDifferent']
        ),
        'data-brick' => $this->getMappedBricksStatistic(
          'codeStatistics.dataBricks',
          $brick_stats['dataBricks']['numTotal'],
          $brick_stats['dataBricks']['different']['numDifferent']
        ),
        'device-brick' => $this->getMappedBricksStatistic(
          'codeStatistics.deviceBricks',
          $brick_stats['deviceBricks']['numTotal'],
          $brick_stats['deviceBricks']['different']['numDifferent']
        ),
        'special-brick' => $this->getMappedBricksStatistic(
          'codeStatistics.specialBricks',
          $brick_stats['specialBricks']['numTotal'],
          $brick_stats['specialBricks']['different']['numDifferent']
        ),
      ],
    ]);
  }

  private function getMappedProjectStatistic(string $trans_id, int $total_number): array
  {
    return [
      'name' => $this->trans($trans_id),
      'total-number' => $total_number,
    ];
  }

  private function getMappedBricksStatistic(string $trans_id, int $total_number, int $different): array
  {
    return [
      'name' => $this->trans($trans_id),
      'total-number' => $total_number,
      'different' => $different,
    ];
  }

  private function trans(string $id): string
  {
    return $this->translator->trans($id, [], 'catroweb');
  }
}
