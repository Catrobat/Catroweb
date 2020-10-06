<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\CatrobatCode\Parser\CatrobatCodeParser;
use App\Catrobat\Services\ExtractedFileRepository;
use App\Entity\Program;
use App\Entity\ProgramManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CodeStatisticsController extends AbstractController
{
  private ProgramManager $program_manager;
  private ExtractedFileRepository $extracted_file_repository;
  private CatrobatCodeParser $code_parser;
  private TranslatorInterface $translator;

  public function __construct(ProgramManager $program_manager,
                              ExtractedFileRepository $extracted_file_repository,
                              CatrobatCodeParser $code_parser,
                              TranslatorInterface $translator)
  {
    $this->program_manager = $program_manager;
    $this->extracted_file_repository = $extracted_file_repository;
    $this->code_parser = $code_parser;
    $this->translator = $translator;
  }

  /**
   * @Route("/project/{id}/code_statistics", name="code_statistics", methods={"GET"})
   */
  public function view(string $id): Response
  {
    // Todo: create useful data structures in CodeStatistic.php
    // Todo: add more statistic
    // Todo: better display of statistics -> E.g. Dr.Scratch

    $parsed_program = null;

    /** @var Program|null $program */
    $program = $this->program_manager->find($id);

    if (null !== $program)
    {
      $extracted_file = $this->extracted_file_repository->loadProgramExtractedFile($program);
      if (null !== $extracted_file)
      {
        $parsed_program = $this->code_parser->parse($extracted_file);
      }
    }

    if (null === $parsed_program)
    {
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
        'special-brick' => $this->getMappedBricksStatistic(
        'codeStatistics.specialBricks',
          $brick_stats['specialBricks']['numTotal'],
          $brick_stats['specialBricks']['different']['numDifferent']
        ),
      ],
      'ctscore_data' => [
        'Abstraction and problem decomposition' => $this->getMappedCTscoreStatistic('codeview.abstraction', $stats->getAbstractionStatistic().'/3'),
        'Parallelism' => $this->getMappedCTscoreStatistic('codeview.parallelism', $stats->getParallelismStatistic().'/3'),
        'Logical thinking' => $this->getMappedCTscoreStatistic('codeview.logical', $stats->getLogicalStatistic().'/3'),
        'Synchronization' => $this->getMappedCTscoreStatistic('codeview.syn', $stats->getSynStatistic().'/3'),
        'Flow control' => $this->getMappedCTscoreStatistic('codeview.flow', $stats->getFlowStatistic().'/3'),
        'User interactivity' => $this->getMappedCTscoreStatistic('codeview.user', $stats->getUserStatistic().'/3'),
        'Data representation' => $this->getMappedCTscoreStatistic('codeview.data', $stats->getDataStatistic().'/3'),
      ],
      'ctscore_data_sum' => $this->getMappedCTscoreStatistic('codeview.total', $stats->getCTScoreSum().'/12'),
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

  private function getMappedCTscoreStatistic(string $trans_id, string $points): array
  {
    return [
      'name' => $this->trans($trans_id),
      'points' => $points,
    ];
  }

  private function trans(string $id): string
  {
    return $this->translator->trans($id, [], 'catroweb');
  }
}
