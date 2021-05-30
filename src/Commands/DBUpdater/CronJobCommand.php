<?php

namespace App\Commands\DBUpdater;

use App\Commands\Helpers\CommandHelper;
use App\Entity\CronJob;
use App\Repository\CronJobRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronJobCommand extends Command
{
  /**
   * @var string|null
   *
   * @override from Command
   */
  protected static $defaultName = 'catrobat:cronjob';

  protected EntityManagerInterface $entity_manager;

  protected CronJobRepository $cron_job_repository;

  public function __construct(EntityManagerInterface $entity_manager, CronJobRepository $cron_job_repository)
  {
    parent::__construct();
    $this->entity_manager = $entity_manager;
    $this->cron_job_repository = $cron_job_repository;
  }

  protected function configure(): void
  {
    $this->setName(self::$defaultName)
      ->setDescription('Executing Cron Jobs')
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->runCronJob(
      'add_diamond_user_user_achievements',
      ['bin/console', 'catrobat:workflow:achievement:diamond_user'],
      ['timeout' => 86400], // 24h
      '1 week',
      $output
    );

    $this->runCronJob(
      'add_gold_user_user_achievements',
      ['bin/console', 'catrobat:workflow:achievement:gold_user'],
      ['timeout' => 86400], // 24h
      '1 week',
      $output
    );

    $this->runCronJob(
      'add_silver_user_user_achievements',
      ['bin/console', 'catrobat:workflow:achievement:silver_user'],
      ['timeout' => 86400], // 24h
      '1 week',
      $output
    );

    // Fix inconsistencies that should not happen :)

    $this->runCronJob(
      'add_bronze_user_user_achievements',
      ['bin/console', 'catrobat:workflow:achievement:bronze_user'],
      ['timeout' => 86400], // 24h
      '1 year',
      $output
    );

    $this->runCronJob(
      'add_verified_developer_user_achievements',
      ['bin/console', 'catrobat:workflow:achievement:verified_developer'],
      ['timeout' => 86400], // 24h
      '1 year',
      $output
    );

    $this->runCronJob(
      'add_perfect_profile_user_achievements',
      ['bin/console', 'catrobat:workflow:achievement:perfect_profile'],
      ['timeout' => 86400], // 24h
      '1 year',
      $output
    );

    return 0;
  }

  /**
   * @throws Exception
   */
  protected function runCronJob(string $name, array $command, array $config, string $interval, OutputInterface $output): bool
  {
    $output->writeln("---\nStarting CronJob: ".$name);

    $cron_job = $this->getOrCreateCronJob($name);
    $cron_job->setCroninterval($interval);

    if (!is_null($cron_job->getStartAt()) && !is_null($cron_job->getEndAt())) {
      $next_run_at = $cron_job->getStartAt()->modify('+'.$cron_job->getCroninterval());
      if ($next_run_at > new DateTime('now') && 0 === $cron_job->getResultCode()) {
        $output->writeln('Job skipped');

        return false;
      }
    }

    $cron_job->setState('run');
    $cron_job->setStartAt(new DateTime('now'));
    $this->entity_manager->persist($cron_job);
    $this->entity_manager->flush();

    $result_code = CommandHelper::executeShellCommand($command, $config, '', $output);

    $cron_job->setResultCode($result_code);
    $cron_job->setEndAt(new DateTime('now'));
    $cron_job->setState('idle');
    $this->entity_manager->persist($cron_job);
    $this->entity_manager->flush();

    $output->writeln('Job finished with code: '.$result_code);

    return true;
  }

  protected function getOrCreateCronJob(string $name): CronJob
  {
    $cron_job = $this->cron_job_repository->findByName($name) ?? new CronJob();

    return $cron_job->setName($name);
  }
}
