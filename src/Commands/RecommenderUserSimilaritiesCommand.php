<?php

namespace App\Commands;

use App\Catrobat\RecommenderSystem\RecommenderManager;
use App\Commands\Helpers\RecommenderFileLock;
use App\Entity\UserManager;
use App\Utils\TimeUtils;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecommenderUserSimilaritiesCommand extends Command
{
  protected static $defaultName = 'catrobat:recommender:compute';

  private UserManager $user_manager;

  private RecommenderManager $recommender_manager;

  private EntityManagerInterface $entity_manager;

  private string $app_root_dir;

  private ?OutputInterface $output;

  private ?RecommenderFileLock $migration_file_lock;

  public function __construct(UserManager $user_manager, RecommenderManager $recommender_manager,
                              EntityManagerInterface $entity_manager, string $kernel_root_dir)
  {
    parent::__construct();
    $this->user_manager = $user_manager;
    $this->recommender_manager = $recommender_manager;
    $this->entity_manager = $entity_manager;
    $this->app_root_dir = $kernel_root_dir;
    $this->output = null;
    $this->migration_file_lock = null;
  }

  /**
   * @param mixed $signal_number
   */
  public function signalHandler($signal_number): void
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
        $this->output->writeln('[SignalHandler] Signal '.$signal_number.' detected');
    }

    $this->migration_file_lock->unlock();
    exit(-1);
  }

  protected function configure(): void
  {
    $this->setName('catrobat:recommender:compute')
      ->setDescription('Computes and updates user similarities in database needed for user-based (Collaborative Filtering) recommendations')
      ->addArgument('type', InputArgument::REQUIRED, 'States the type of similarity to compute, value can be either "like", "remix" or "all"')
    ;
  }

  /**
   * @throws DBALException
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    declare(ticks=1);
    $this->migration_file_lock = new RecommenderFileLock($this->app_root_dir, $output);
    $this->output = $output;
    pcntl_signal(SIGTERM, [$this, 'signalHandler']);
    pcntl_signal(SIGHUP, [$this, 'signalHandler']);
    pcntl_signal(SIGINT, [$this, 'signalHandler']);
    pcntl_signal(SIGUSR1, [$this, 'signalHandler']);

    $this->computeUserSimilarities($output, $input->getArgument('type'));

    return 0;
  }

  /**
   * @param mixed $type
   *
   * @throws DBALException
   * @throws Exception
   */
  private function computeUserSimilarities(OutputInterface $output, $type): void
  {
    $computation_start_time = TimeUtils::getDateTime();
    $progress_bar_format_verbose = '%current%/%max% [%bar%] %percent:3s%% | Elapsed: %elapsed:6s% | Status: %message%';

    $progress_bar = new ProgressBar($output);
    $progress_bar->setFormat($progress_bar_format_verbose);

    $this->migration_file_lock->lock();
    $progress_bar->setMessage('Remove all old user relations!');
    $progress_bar->start();
    $progress_bar->display();

    if (in_array($type, ['like', 'all'], true))
    {
      $this->recommender_manager->removeAllUserLikeSimilarityRelations();
      $this->entity_manager->clear();
      $this->recommender_manager->computeUserLikeSimilarities($progress_bar);
    }

    if (in_array($type, ['remix', 'all'], true))
    {
      $this->recommender_manager->removeAllUserRemixSimilarityRelations();
      $this->entity_manager->clear();
      $this->recommender_manager->computeUserRemixSimilarities($progress_bar);
    }

    $this->migration_file_lock->unlock();

    $duration = TimeUtils::getTimestamp() - $computation_start_time->getTimestamp();
    $progress_bar->setMessage('');
    $progress_bar->finish();
    $output->writeln('');
    $output->writeln('<info>Finished similarity computation (Duration: '.$duration.'s)</info>');
  }
}
