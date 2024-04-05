<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\Project\Remix\ProgramRemixRelation;
use App\DB\EntityRepository\Project\ProgramLikeRepository;
use App\DB\EntityRepository\Project\ProgramRemixRepository;
use App\DB\EntityRepository\Project\ProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:update:popularity', description: 'Updating the popularity score of projects')]
class UpdateProjectPopularityCommand extends Command
{
  final public const BATCH_SIZE = 1000;

  // Weights for the popularity score computation
  final public const VIEWS_W = 10;
  final public const DOWNLOADS_W = 30;
  final public const REMIXES_W = 45;
  final public const REACTIONS_W = 15;

  public function __construct(protected EntityManagerInterface $entity_manager, protected ProgramRepository $program_repository, protected ProgramRemixRepository $program_remix_repository, protected ProgramLikeRepository $program_like_repository)
  {
    parent::__construct();
  }

  // Compute and update popularity score for every project
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $program_count = $this->program_repository->countProjects();
    $offset = 0;
    $min_max_values = $this->getMinMaxValues();
    while ($offset < $program_count) {
      $programs = $this->program_repository->getProjects(limit: self::BATCH_SIZE, offset: $offset);
      foreach ($programs as $program) {
        $popularity = $this->computePopularity($program, $min_max_values);
        $program->setPopularity($popularity);
        $this->entity_manager->persist($program);
      }
      $offset += self::BATCH_SIZE;
    }
    $this->entity_manager->flush();
    $output->writeln('Popularity scores have been updated');

    return Command::SUCCESS;
  }

  protected function computePopularity(Program $program, array $min_max_values): float
  {
    $normalized_data = $this->getNormalizedData($program, $min_max_values);

    return round($normalized_data['views'] * self::VIEWS_W + $normalized_data['downloads'] * self::DOWNLOADS_W + $normalized_data['remixes'] * self::REMIXES_W + $normalized_data['reactions'] * self::REACTIONS_W, 2);
  }

  protected function getNormalizedData(Program $program, array $min_max_values): array
  {
    return [
      'views' => $this->scale($program->getViews(), $min_max_values['views_min'], $min_max_values['views_max']),
      'downloads' => $this->scale($program->getDownloads(), $min_max_values['downloads_min'], $min_max_values['downloads_max']),
      'remixes' => $this->scale(count($program->getCatrobatRemixDescendantRelations()), $min_max_values['remixes_min'], $min_max_values['remixes_max']),
      'reactions' => $this->scale(count($program->getLikes()), $min_max_values['reactions_min'], $min_max_values['reactions_max']),
    ];
  }

  // Scale the data into a range between 0 and 1
  // Currently just min max scaling
  protected function scale(int $x, int $min, int $max): float
  {
    if (0 == $max - $min) {
      return 0;
    }

    return ($x - $min) / ($max - $min);
  }

  // Get minimum and maximum values of all categories relevant for pupularity score
  protected function getMinMaxValues(): array
  {
    return $this->getMinMaxViews() + $this->getMinMaxDownloads() + $this->getMinMaxRemixes() + $this->getMinMaxReactions();
  }

  // Get minimum and maximum values of project views
  protected function getMinMaxViews(): array
  {
    $query_builder = $this->program_repository->createQueryBuilder('e');
    $query_builder->select($query_builder->expr()->min('e.views'));
    $min = $query_builder->getQuery()->getResult()[0][1];
    $query_builder->select($query_builder->expr()->max('e.views'));
    $max = $query_builder->getQuery()->getResult()[0][1];

    return [
      'views_min' => $min,
      'views_max' => $max,
    ];
  }

  // Get minimum and maximum values of project downloads
  protected function getMinMaxDownloads(): array
  {
    $query_builder = $this->program_repository->createQueryBuilder('e');
    $query_builder->select($query_builder->expr()->min('e.downloads'));
    $min = $query_builder->getQuery()->getResult()[0][1];
    $query_builder->select($query_builder->expr()->max('e.downloads'));
    $max = $query_builder->getQuery()->getResult()[0][1];

    return [
      'downloads_min' => $min,
      'downloads_max' => $max,
    ];
  }

  // Get minimum and maximum values of project remixes
  protected function getMinMaxRemixes(): array
  {
    $query_builder = $this->entity_manager->createQueryBuilder();
    $query_builder
      ->select('COUNT(r.ancestor_id) as count')
      ->from(Program::class, 'p')
      ->leftJoin(ProgramRemixRelation::class, 'r', Join::WITH, 'p.id = r.ancestor_id')
      ->groupBy('r.ancestor_id')
      ->orderBy('count', 'DESC')
    ;
    $max = $query_builder->getQuery()->getResult()[0]['count'];
    $query_builder->orderBy('count', 'ASC');
    $min = $query_builder->getQuery()->getResult()[0]['count'];

    return [
      'remixes_min' => $min,
      'remixes_max' => $max,
    ];
  }

  // Get minimum and maximum values of project reactions
  protected function getMinMaxReactions(): array
  {
    $query_builder = $this->entity_manager->createQueryBuilder();

    $query_builder
      ->select('COUNT(e.program_id) as count')
      ->from(Program::class, 'p')
      ->leftJoin(ProgramLike::class, 'e', Join::WITH, 'p.id = e.program_id')
      ->groupBy('e.program_id')
      ->orderBy('count', 'DESC')
      ->setMaxResults(1)
    ;

    $max = $query_builder->getQuery()->getResult()[0]['count'];
    $query_builder->orderBy('count', 'ASC');
    $min = $query_builder->getQuery()->getResult()[0]['count'];

    return [
      'reactions_min' => $min,
      'reactions_max' => $max,
    ];
  }
}
