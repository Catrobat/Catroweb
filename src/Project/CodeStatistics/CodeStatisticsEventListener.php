<?php

declare(strict_types=1);

namespace App\Project\CodeStatistics;

use App\Project\Event\ProjectAfterInsertEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ProjectAfterInsertEvent::class, method: 'onProjectAfterInsert')]
class CodeStatisticsEventListener
{
  public function __construct(
    private readonly CodeStatisticsParser $parser,
    private readonly EntityManagerInterface $entity_manager,
    private readonly LoggerInterface $logger,
  ) {
  }

  public function onProjectAfterInsert(ProjectAfterInsertEvent $event): void
  {
    try {
      $extracted_file = $event->getExtractedFile();
      $project = $event->getProjectEntity();

      $code_xml_path = $extracted_file->getPath().'code.xml';
      if (!file_exists($code_xml_path)) {
        return;
      }

      $stats = $this->parser->parse($code_xml_path);
      $stats->setProject($project);

      $this->entity_manager->persist($stats);
      $this->entity_manager->flush();
    } catch (\Throwable $e) {
      $this->logger->error('CodeStatisticsEventListener failed: '.$e->getMessage());
    }
  }
}
