<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\ProjectDownloads;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Recomputes the leaderboard scores of all users.
 *
 * Both scores are a Bayesian-smoothed average of downloads per public project, in hundredths:
 *   score = round(SCORE_SCALE * (downloads + PRIOR_WEIGHT * global_avg) / (projects + PRIOR_WEIGHT))
 * Users whose projects were never downloaded (by others, for the weekly score) get no score.
 *
 * - ranking_score:        all-time project download counters
 * - ranking_score_recent: downloads of the last RECENT_WINDOW_DAYS days (owner's own downloads excluded),
 *                         averaged over the projects that were downloaded in that window
 *
 * The smoothing pulls users with few projects towards the global average, so one lucky project
 * does not outrank a consistently good creator. See docs/architecture/adr-user-leaderboard-score.md.
 */
#[AsCommand(name: 'catrobat:update:userranking', description: 'Recomputes the all-time and weekly leaderboard scores of all users')]
class UpdateUserRankingCommand extends Command
{
  final public const int PRIOR_WEIGHT = 5;

  final public const int RECENT_WINDOW_DAYS = 7;

  final public const int SCORE_SCALE = 100;

  public function __construct(protected EntityManagerInterface $entity_manager)
  {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Compute and print the top users without writing scores');
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $since = new \DateTime(sprintf('-%d days', self::RECENT_WINDOW_DAYS));
    $all_time = self::computeScores($this->fetchAllTimeStats());
    $recent = self::computeScores($this->fetchRecentStats($since));

    $output->writeln(sprintf('All-time: %d users scored.', count($all_time)));
    $output->writeln(sprintf('Last %d days: %d users scored.', self::RECENT_WINDOW_DAYS, count($recent)));

    if ($input->getOption('dry-run')) {
      $this->printTop($output, 'All-time top 10', $all_time);
      $this->printTop($output, 'Weekly top 10', $recent);
      $output->writeln('Dry run: nothing written.');

      return Command::SUCCESS;
    }

    $this->entity_manager->wrapInTransaction(function () use ($all_time, $recent): void {
      $this->writeScores('ranking_score', $all_time);
      $this->writeScores('ranking_score_recent', $recent);
    });

    $output->writeln('User rankings have been updated.');

    return Command::SUCCESS;
  }

  /**
   * @param array $rows rows of user_id, downloads, projects
   *
   * @return array<string, int> user id => score, only users with at least one download
   */
  public static function computeScores(array $rows): array
  {
    $total_downloads = 0;
    $total_projects = 0;
    foreach ($rows as $row) {
      $total_downloads += (int) $row['downloads'];
      $total_projects += (int) $row['projects'];
    }

    if (0 === $total_projects) {
      return [];
    }

    $prior_mean = $total_downloads / $total_projects;
    $scores = [];
    foreach ($rows as $row) {
      if ((int) $row['downloads'] > 0) {
        $scores[(string) $row['user_id']] = self::smoothedScore((int) $row['downloads'], (int) $row['projects'], $prior_mean);
      }
    }

    return $scores;
  }

  public static function smoothedScore(int $downloads, int $projects, float $prior_mean): int
  {
    return (int) round(self::SCORE_SCALE * ($downloads + self::PRIOR_WEIGHT * $prior_mean) / ($projects + self::PRIOR_WEIGHT));
  }

  /**
   * @return array rows of user_id, downloads, projects
   */
  private function fetchAllTimeStats(): array
  {
    return $this->entity_manager->createQueryBuilder()
      ->select('IDENTITY(p.user) AS user_id', 'SUM(p.downloads) AS downloads', 'COUNT(p.id) AS projects')
      ->from(Project::class, 'p')
      ->where('p.visible = true AND p.auto_hidden = false AND p.private = false AND p.debug_build = false')
      ->groupBy('p.user')
      ->getQuery()
      ->getArrayResult()
    ;
  }

  /**
   * @return array rows of user_id, downloads, projects
   */
  private function fetchRecentStats(\DateTime $since): array
  {
    return $this->entity_manager->createQueryBuilder()
      ->select('IDENTITY(p.user) AS user_id', 'COUNT(d.id) AS downloads', 'COUNT(DISTINCT p.id) AS projects')
      ->from(ProjectDownloads::class, 'd')
      ->innerJoin('d.project', 'p')
      ->where('p.visible = true AND p.auto_hidden = false AND p.private = false AND p.debug_build = false')
      ->andWhere('d.downloaded_at >= :since')
      ->andWhere('d.type = :type')
      ->andWhere('d.user IS NULL OR d.user != p.user')
      ->setParameter('since', $since)
      ->setParameter('type', ProjectDownloads::TYPE_PROJECT)
      ->groupBy('p.user')
      ->getQuery()
      ->getArrayResult()
    ;
  }

  /**
   * @param array<string, int> $scores
   */
  private function writeScores(string $field, array $scores): void
  {
    $this->entity_manager
      ->createQuery(sprintf('UPDATE %s u SET u.%s = 0 WHERE u.%s > 0', User::class, $field, $field))
      ->execute()
    ;

    // ponytail: one UPDATE per scored user; batch with CASE WHEN if the nightly run gets too slow
    $query = $this->entity_manager->createQuery(sprintf('UPDATE %s u SET u.%s = :score WHERE u.id = :id', User::class, $field));
    foreach ($scores as $user_id => $score) {
      $query->setParameter('score', $score)->setParameter('id', (string) $user_id)->execute();
    }
  }

  /**
   * @param array<string, int> $scores
   */
  private function printTop(OutputInterface $output, string $title, array $scores): void
  {
    arsort($scores);
    $output->writeln($title.':');
    foreach (array_slice($scores, 0, 10, true) as $user_id => $score) {
      $output->writeln(sprintf('  %s  %d', (string) $user_id, $score));
    }
  }
}
