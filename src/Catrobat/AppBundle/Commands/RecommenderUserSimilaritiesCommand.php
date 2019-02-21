<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\RecommenderSystem\RecommenderManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Catrobat\AppBundle\Entity\UserManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Helper\ProgressBar;


/**
 * Class RecommenderUserSimilaritiesCommand
 * @package Catrobat\AppBundle\Commands
 */
class RecommenderUserSimilaritiesCommand extends ContainerAwareCommand
{
  /**
   * @var UserManager
   */
  private $user_manager;

  /**
   * @var RecommenderManager
   */
  private $recommender_manager;

  /**
   * @var EntityManager
   */
  private $entity_manager;

  /**
   * @var string
   */
  private $app_root_dir;

  /**
   * @var OutputInterface
   */
  private $output;

  /**
   * @var RecommenderFileLock
   */
  private $migration_file_lock;

  /**
   * RecommenderUserSimilaritiesCommand constructor.
   *
   * @param UserManager        $user_manager
   * @param RecommenderManager $recommender_manager
   * @param EntityManager      $entity_manager
   * @param                    $app_root_dir
   */
  public function __construct(UserManager $user_manager, RecommenderManager $recommender_manager,
                              EntityManager $entity_manager, $app_root_dir)
  {
    parent::__construct();
    $this->user_manager = $user_manager;
    $this->recommender_manager = $recommender_manager;
    $this->entity_manager = $entity_manager;
    $this->app_root_dir = $app_root_dir;
    $this->output = null;
    $this->migration_file_lock = null;
  }

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:recommender:compute')
      ->setDescription('Computes and updates user similarities in database needed for user-based (Collaborative Filtering) recommendations')
      ->addArgument('type', InputArgument::REQUIRED, 'States the type of similarity to compute, value can be either "like", "remix" or "all"')
      ->addOption('cronjob');
  }

  /**
   * @param $signal_number
   */
  public function signalHandler($signal_number)
  {
    $this->output->writeln('[SignalHandler] Called Signal Handler');
    switch ($signal_number)
    {
      case SIGTERM:
        $this->output->writeln('[SignalHandler] User aborted the process');
        break;
      case SIGHUP:
        $this->output->writeln('[SignalHandler] SigHup detected');
        break;
      case SIGINT:
        $this->output->writeln('[SignalHandler] SigInt detected');
        break;
      case SIGUSR1:
        $this->output->writeln('[SignalHandler] SigUsr1 detected');
        break;
      default:
        $this->output->writeln('[SignalHandler] Signal ' . $signal_number . ' detected');
    }

    $this->migration_file_lock->unlock();
    exit(-1);
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   * @throws \Doctrine\Common\Persistence\Mapping\MappingException
   * @throws \Doctrine\DBAL\DBALException
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    declare(ticks=1);
    $this->migration_file_lock = new RecommenderFileLock($this->app_root_dir, $output);
    $this->output = $output;
    pcntl_signal(SIGTERM, [$this, 'signalHandler']);
    pcntl_signal(SIGHUP, [$this, 'signalHandler']);
    pcntl_signal(SIGINT, [$this, 'signalHandler']);
    pcntl_signal(SIGUSR1, [$this, 'signalHandler']);

    $this->computeUserSimilarities($output, $input->getArgument('type'), $input->getOption('cronjob'));
  }

  /**
   * @param OutputInterface $output
   * @param                 $type
   * @param                 $is_cronjob
   *
   * @throws \Doctrine\Common\Persistence\Mapping\MappingException
   * @throws \Doctrine\DBAL\DBALException
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   * @throws \Exception
   */
  private function computeUserSimilarities(OutputInterface $output, $type, $is_cronjob)
  {
    $computation_start_time = new \DateTime();
    $progress_bar_format_verbose = '%current%/%max% [%bar%] %percent:3s%% | Elapsed: %elapsed:6s% | Status: %message%';

    $progress_bar = $is_cronjob ? new CronjobProgressWriter($output) : new ProgressBar($output);
    $progress_bar->setFormat($progress_bar_format_verbose);

    $this->migration_file_lock->lock();
    $progress_bar->setMessage('Remove all old user relations!');
    $progress_bar->start();
    $progress_bar->display();

    if (in_array($type, ['like', 'all']))
    {
      $this->recommender_manager->removeAllUserLikeSimilarityRelations();
      $this->entity_manager->clear();
      $this->recommender_manager->computeUserLikeSimilarities($progress_bar);
    }

    if (in_array($type, ['remix', 'all']))
    {
      $this->recommender_manager->removeAllUserRemixSimilarityRelations();
      $this->entity_manager->clear();
      $this->recommender_manager->computeUserRemixSimilarities($progress_bar);
    }

    $this->migration_file_lock->unlock();

    $duration = (new \DateTime())->getTimestamp() - $computation_start_time->getTimestamp();
    $progress_bar->setMessage('');
    $progress_bar->finish();
    $output->writeln('');
    $output->writeln('<info>Finished similarity computation (Duration: ' . $duration . 's)</info>');
  }
}
