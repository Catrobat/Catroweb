<?php

declare(strict_types=1);

namespace App\System\Cron;

/**
 * Every periodic task. The scheduler ticks both lanes every few minutes; a task runs when its interval has passed
 * since its last successful start (the cronjob table), so restarts and deploys don't reset the clock.
 * Names are the cronjob table's primary key: keep them unique and stable.
 */
final class CronTasks
{
  private const int HOUR = 3600;
  private const int DAY = 24 * self::HOUR;
  private const int WEEK = 7 * self::DAY;

  /** @return list<CronTask> */
  public static function all(): array
  {
    $task = static fn (string $name, string $command, string $interval, int $timeout, bool $slow = false): CronTask => new CronTask($name, ['bin/console', $command], $interval, $timeout, $slow);

    return [
      $task('Update project popularity scores', 'catrobat:update:popularity', '6 hours', self::HOUR),
      $task('Update user rankings', 'catrobat:update:userranking', '1 day', self::HOUR),
      $task('Clean old extracted project files', 'catrobat:clean:extracts', '1 day', self::HOUR),
      $task('Clean compressed project files', 'catrobat:clean:compressed', '1 week', self::HOUR),
      $task("Remove and add new projects to the random projects' category", 'catrobat:workflow:update_random_project_category', '1 week', self::DAY),
      $task('Add diamond_user UserAchievements', 'catrobat:workflow:achievement:diamond_user', '1 week', self::DAY),
      $task('Add gold_user UserAchievements', 'catrobat:workflow:achievement:gold_user', '1 week', self::HOUR),
      $task('Add silver_user UserAchievements', 'catrobat:workflow:achievement:silver_user', '1 week', self::HOUR),
      $task('Add verified_developer (silver) UserAchievements', 'catrobat:workflow:achievement:verified_developer_silver', '1 week', self::HOUR),
      $task('Add verified_developer (gold) UserAchievements', 'catrobat:workflow:achievement:verified_developer_gold', '1 week', self::HOUR),
      $task('Retroactively unlock custom translation achievements', 'catrobat:workflow:achievement:translation', '1 month', self::DAY),
      $task('Add bronze_user UserAchievements', 'catrobat:workflow:achievement:bronze_user', '1 year', self::WEEK),
      $task('Add perfect_profile UserAchievements', 'catrobat:workflow:achievement:perfect_profile', '1 year', self::WEEK),
      $task('Clean up background jobs', 'catrobat:background-jobs:clean', '1 hour', self::HOUR),
      $task('Garbage collect orphaned assets', 'catrobat:gc-assets', '1 day', self::HOUR),
      $task('Delete old notifications', 'catrobat:clean:notifications', '1 day', self::HOUR),
      $task('Delete expired refresh tokens', 'catrobat:clean:refresh-tokens', '1 day', self::HOUR),
      $task('Warn about and delete empty, inactive studios', 'catrobat:clean:empty-studios', '1 day', self::HOUR),

      // Slow lane: whole-table or whole-disk sweeps
      $task('Delete expired projects based on retention rules', 'catrobat:storage:lifecycle', '1 day', self::DAY, true),
      $task('Detect broken projects', 'catrobat:workflow:detect_broken_projects', '1 day', self::DAY, true),
      $task('Re-sanitize user-generated content', 'catro:moderation:sanitize-existing', '1 month', self::DAY, true),
      $task('(Re-)Add project extensions', 'catrobat:workflow:project:refresh_extensions', '1 year', self::WEEK, true),
      $task('Clean unverified user accounts', 'catrobat:clean:unverified-users', '1 day', 3 * self::HOUR, true),
      $task('Delete Scratch placeholder accounts without projects', 'catrobat:clean:scratch-placeholders', '1 day', 3 * self::HOUR, true),
      $task('Warn about and delete inactive empty accounts', 'catrobat:clean:inactive-users', '1 day', 3 * self::HOUR, true),
      new CronTask('Delete content hidden by moderation for 14 days', ['bin/console', 'catro:moderation:purge-hidden', '--apply'], '1 day', 3 * self::HOUR, true),
    ];
  }

  public static function find(string $name): ?CronTask
  {
    foreach (self::all() as $task) {
      if ($task->name === $name) {
        return $task;
      }
    }

    return null;
  }
}
