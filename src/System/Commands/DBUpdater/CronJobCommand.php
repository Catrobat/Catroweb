<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater;

use App\DB\Entity\System\CronJob;
use App\DB\EntityRepository\System\CronJobRepository;
use App\System\Commands\Helpers\CommandHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:cronjob', description: 'Executing Cron Jobs')]
class CronJobCommand extends Command
{
  protected const int ONE_MINUTE_IN_SECONDS = 60;

  protected const int ONE_HOUR_IN_SECONDS = self::ONE_MINUTE_IN_SECONDS * 60;

  protected const int ONE_DAY_IN_SECONDS = self::ONE_HOUR_IN_SECONDS * 24;

  protected const int ONE_WEEK_IN_SECONDS = self::ONE_DAY_IN_SECONDS * 7;

  public function __construct(protected EntityManagerInterface $entity_manager, protected CronJobRepository $cron_job_repository)
  {
    parent::__construct();
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output->writeln('App env: '.$_ENV['APP_ENV']);

    $this->recoverStuckJobs($output);

    // Fast, critical jobs first — these should never be blocked by slow tasks

    $this->runCronJob(
      'Update project popularity scores',
      ['bin/console', 'catrobat:update:popularity'],
      ['timeout' => self::ONE_HOUR_IN_SECONDS],
      '6 hours',
      $output
    );

    $this->runCronJob(
      'Update user rankings',
      ['bin/console', 'catrobat:update:userranking'],
      ['timeout' => self::ONE_HOUR_IN_SECONDS],
      '1 day',
      $output
    );

    // Daily storage maintenance

    $this->runCronJob(
      'Delete expired projects based on retention rules',
      ['bin/console', 'catrobat:storage:lifecycle'],
      ['timeout' => self::ONE_DAY_IN_SECONDS],
      '1 day',
      $output
    );

    $this->runCronJob(
      'Clean old extracted project files',
      ['bin/console', 'catrobat:clean:extracts'],
      ['timeout' => self::ONE_HOUR_IN_SECONDS],
      '1 day',
      $output
    );

    $this->runCronJob(
      'Garbage collect orphaned assets',
      ['bin/console', 'catrobat:gc-assets'],
      ['timeout' => self::ONE_DAY_IN_SECONDS],
      '1 week',
      $output
    );

    $this->runCronJob(
      'Clean compressed project files',
      ['bin/console', 'catrobat:clean:compressed'],
      ['timeout' => self::ONE_HOUR_IN_SECONDS],
      '1 week',
      $output
    );

    // Daily project integrity

    $this->runCronJob(
      'Detect broken projects',
      ['bin/console', 'catrobat:workflow:detect_broken_projects'],
      ['timeout' => self::ONE_DAY_IN_SECONDS],
      '1 day',
      $output
    );

    // Weekly project categories

    $this->runCronJob(
      "Remove and add new projects to the random projects' category",
      ['bin/console', 'catrobat:workflow:update_random_project_category'],
      ['timeout' => self::ONE_DAY_IN_SECONDS],
      '1 week',
      $output
    );

    // Weekly achievements

    $this->runCronJob(
      'Add diamond_user UserAchievements',
      ['bin/console', 'catrobat:workflow:achievement:diamond_user'],
      ['timeout' => self::ONE_DAY_IN_SECONDS],
      '1 week',
      $output
    );

    $this->runCronJob(
      'Add gold_user UserAchievements',
      ['bin/console', 'catrobat:workflow:achievement:gold_user'],
      ['timeout' => self::ONE_HOUR_IN_SECONDS],
      '1 week',
      $output
    );

    $this->runCronJob(
      'Add silver_user UserAchievements',
      ['bin/console', 'catrobat:workflow:achievement:silver_user'],
      ['timeout' => self::ONE_HOUR_IN_SECONDS],
      '1 week',
      $output
    );

    $this->runCronJob(
      'Add verified_developer (silver) UserAchievements',
      ['bin/console', 'catrobat:workflow:achievement:verified_developer_silver'],
      ['timeout' => self::ONE_HOUR_IN_SECONDS],
      '1 week',
      $output
    );

    $this->runCronJob(
      'Add verified_developer (gold) UserAchievements',
      ['bin/console', 'catrobat:workflow:achievement:verified_developer_gold'],
      ['timeout' => self::ONE_HOUR_IN_SECONDS],
      '1 week',
      $output
    );

    // Monthly maintenance

    $this->runCronJob(
      'Re-sanitize user-generated content',
      ['bin/console', 'catro:moderation:sanitize-existing'],
      ['timeout' => self::ONE_DAY_IN_SECONDS],
      '1 month',
      $output
    );

    $this->runCronJob(
      'Delete old entries in machine translation table',
      ['bin/console', 'catrobat:translation:trim-storage'],
      ['timeout' => self::ONE_DAY_IN_SECONDS],
      '1 month',
      $output
    );

    $this->runCronJob(
      'Retroactively unlock custom translation achievements',
      ['bin/console', 'catrobat:workflow:achievement:translation'],
      ['timeout' => self::ONE_DAY_IN_SECONDS],
      '1 month',
      $output
    );

    // Yearly consistency checks

    $this->runCronJob(
      'Add bronze_user UserAchievements',
      ['bin/console', 'catrobat:workflow:achievement:bronze_user'],
      ['timeout' => self::ONE_WEEK_IN_SECONDS],
      '1 year',
      $output
    );

    $this->runCronJob(
      'Add perfect_profile UserAchievements',
      ['bin/console', 'catrobat:workflow:achievement:perfect_profile'],
      ['timeout' => self::ONE_WEEK_IN_SECONDS],
      '1 year',
      $output
    );

    $this->runCronJob(
      '(Re-)Add project extensions',
      ['bin/console', 'catrobat:workflow:project:refresh_extensions'],
      ['timeout' => self::ONE_WEEK_IN_SECONDS],
      '1 year',
      $output
    );

    // Slow jobs last — these can take hours and should not block anything above

    $this->runCronJob(
      'Clean unverified user accounts',
      ['bin/console', 'catrobat:clean:unverified-users'],
      ['timeout' => 3 * self::ONE_HOUR_IN_SECONDS],
      '1 day',
      $output
    );

    // Log maintenance (fast, but lowest priority)

    $this->runCronJob(
      'Archive log files',
      ['bin/console', 'catrobat:logs:archive'],
      ['timeout' => self::ONE_HOUR_IN_SECONDS],
      '1 day',
      $output
    );

    $this->runCronJob(
      'Clean old log files',
      ['bin/console', 'catrobat:clean:logs'],
      ['timeout' => self::ONE_HOUR_IN_SECONDS],
      '1 week',
      $output
    );

    return 0;
  }

  /**
   * Auto-recover jobs stuck in 'run' state longer than twice their timeout.
   * This happens when the server reboots or a process crashes without cleanup.
   */
  protected function recoverStuckJobs(OutputInterface $output): void
  {
    $stuck_jobs = $this->cron_job_repository->findBy(['state' => 'run']);

    foreach ($stuck_jobs as $job) {
      $start = $job->getStartAt();
      if (null === $start) {
        continue;
      }

      $elapsed = (new \DateTime('now'))->getTimestamp() - $start->getTimestamp();
      $max_stuck_time = 2 * max($job->getTimeoutSeconds() ?? self::ONE_HOUR_IN_SECONDS, self::ONE_HOUR_IN_SECONDS);

      if ($elapsed > $max_stuck_time) {
        $output->writeln(sprintf(
          'Auto-recovering stuck job "%s" (stuck for %d minutes)',
          $job->getName(),
          intdiv($elapsed, 60)
        ));
        $job->setState('idle');
        $job->setResultCode(-2);
        $job->setEndAt(new \DateTime('now'));
        $this->entity_manager->persist($job);
      }
    }

    $this->entity_manager->flush();
  }

  /**
   * @throws \Exception
   */
  protected function runCronJob(string $name, array $command, array $config, string $interval, OutputInterface $output): bool
  {
    $output->writeln("---\nStarting CronJob: ".$name);

    $cron_job = $this->getOrCreateCronJob($name);
    $cron_job->setCroninterval($interval);

    if ('run' === $cron_job->getState()) {
      $output->writeln('Still running');

      return false;
    }

    if (!is_null($cron_job->getStartAt()) && !is_null($cron_job->getEndAt())) {
      $next_run_at = $cron_job->getStartAt()->modify('+'.$cron_job->getCroninterval());
      if ($next_run_at > new \DateTime('now') && 0 === $cron_job->getResultCode()) {
        $output->writeln('Job skipped.');

        return false;
      }
    }

    $timeout = $config['timeout'] ?? self::ONE_HOUR_IN_SECONDS;

    $cron_job->setState('run');
    $cron_job->setStartAt(new \DateTime('now'));
    $cron_job->setTimeoutSeconds($timeout);

    $this->entity_manager->persist($cron_job);
    $this->entity_manager->flush();

    $start_time = microtime(true);

    try {
      $result_code = CommandHelper::executeShellCommand($command, $config, '', $output);
    } catch (\RuntimeException) {
      $duration = (int) (microtime(true) - $start_time);
      $cron_job->setResultCode(-1);
      $cron_job->setState('timeout');
      $cron_job->setDurationSeconds($duration);
      $cron_job->setEndAt(new \DateTime('now'));
      $this->entity_manager->persist($cron_job);
      $this->entity_manager->flush();
      $output->writeln('Timeout - Process did not finish!');

      return false;
    }

    $duration = (int) (microtime(true) - $start_time);
    $cron_job->setResultCode($result_code);
    $cron_job->setEndAt(new \DateTime('now'));
    $cron_job->setDurationSeconds($duration);
    $cron_job->setState('idle');

    $this->entity_manager->persist($cron_job);
    $this->entity_manager->flush();

    $output->writeln(sprintf('Job finished with code: %d (took %ds)', $result_code ?? -1, $duration));

    return true;
  }

  protected function getOrCreateCronJob(string $name): CronJob
  {
    $cron_job = $this->cron_job_repository->findByName($name) ?? new CronJob();

    return $cron_job->setName($name);
  }
}
