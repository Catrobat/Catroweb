<?php

declare(strict_types=1);

namespace App\Project\CodeStatistics;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\ProjectCodeStatistics;
use App\Project\CatrobatFile\ExtractedFileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service to retrieve code statistics for a project.
 * Returns persisted stats if available, otherwise parses on-demand and persists the result.
 */
class CodeStatisticsService
{
  public function __construct(
    private readonly CodeStatisticsParser $parser,
    private readonly ExtractedFileRepository $extracted_file_repository,
    private readonly EntityManagerInterface $entity_manager,
    private readonly LoggerInterface $logger,
  ) {
  }

  /**
   * Get the latest code statistics for a project.
   * If no persisted stats exist, attempts to parse on-demand and persist.
   */
  public function getStatistics(Project $project): ?ProjectCodeStatistics
  {
    // Query the latest row directly so lazy collections do not hand us an older
    // in-memory snapshot after additional statistics rows have been persisted.
    /** @var ?ProjectCodeStatistics $latest */
    $latest = $this->entity_manager->getRepository(ProjectCodeStatistics::class)->findOneBy(
      ['project' => $project],
      ['created_at' => 'DESC'],
    );

    if (null !== $latest && CodeStatisticsParser::CURRENT_SCORING_VERSION === $latest->getScoringVersion()) {
      return $latest;
    }

    $refreshed = $this->parseAndPersist($project);

    return $refreshed ?? $latest;
  }

  /**
   * Parse code.xml for a project and persist the statistics.
   */
  private function parseAndPersist(Project $project): ?ProjectCodeStatistics
  {
    try {
      $extracted_file = $this->extracted_file_repository->loadProjectExtractedFile($project);
      if (null === $extracted_file) {
        return null;
      }

      $code_xml_path = $extracted_file->getPath().'code.xml';
      if (!file_exists($code_xml_path)) {
        return null;
      }

      $stats = $this->parser->parse($code_xml_path);
      $stats->setProject($project);

      $this->entity_manager->persist($stats);
      $this->entity_manager->flush();

      return $stats;
    } catch (\Throwable $e) {
      $this->logger->error('On-demand code statistics parsing failed for project '.$project->getId().': '.$e->getMessage());

      return null;
    }
  }
}
