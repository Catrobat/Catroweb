<?php

declare(strict_types=1);

namespace App\Project\CodeStatistics;

use App\DB\Entity\Project\Program;
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
  public function getStatistics(Program $project): ?ProjectCodeStatistics
  {
    $latest = $project->getLatestCodeStatistics();
    if (null !== $latest) {
      return $latest;
    }

    return $this->parseAndPersist($project);
  }

  /**
   * Parse code.xml for a project and persist the statistics.
   */
  private function parseAndPersist(Program $project): ?ProjectCodeStatistics
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
      $stats->setProgram($project);

      $this->entity_manager->persist($stats);
      $this->entity_manager->flush();

      return $stats;
    } catch (\Throwable $e) {
      $this->logger->error('On-demand code statistics parsing failed for project '.$project->getId().': '.$e->getMessage());

      return null;
    }
  }
}
