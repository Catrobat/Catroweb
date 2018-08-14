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

    public function __construct(UserManager $user_manager, RecommenderManager $recommender_manager, EntityManager $entity_manager, $app_root_dir)
    {
        parent::__construct();
        $this->user_manager = $user_manager;
        $this->recommender_manager = $recommender_manager;
        $this->entity_manager = $entity_manager;
        $this->app_root_dir = $app_root_dir;
        $this->output = null;
        $this->migration_file_lock = null;
    }

    protected function configure()
    {
        $this->setName('catrobat:recommender:compute')
            ->setDescription('Computes and updates user similarities in database needed for user-based (Collaborative Filtering) recommendations')
            ->addArgument('type', InputArgument::REQUIRED, 'States the type of similarity to compute, value can be either "like", "remix" or "all"')
            ->addOption('cronjob');
    }

    public function signalHandler($signal_number)
    {
        $this->output->writeln('[SignalHandler] Called Signal Handler');
        switch ($signal_number) {
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        declare(ticks = 1);
        $this->migration_file_lock = new RecommenderFileLock($this->app_root_dir, $output);
        $this->output = $output;
        pcntl_signal(SIGTERM, [$this, 'signalHandler']);
        pcntl_signal(SIGHUP, [$this, 'signalHandler']);
        pcntl_signal(SIGINT, [$this, 'signalHandler']);
        pcntl_signal(SIGUSR1, [$this, 'signalHandler']);

        $this->computeUserSimilarities($output, $input->getArgument('type'), $input->getOption('cronjob'));
    }

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

        if (in_array($type, ['like', 'all'])) {
            $this->recommender_manager->removeAllUserLikeSimilarityRelations();
            $this->entity_manager->clear();
            $this->recommender_manager->computeUserLikeSimilarities($progress_bar);
        }

        if (in_array($type, ['remix', 'all'])) {
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

class CronjobProgressWriter #extends ProgressBar
{
    /**
     * @var OutputInterface
     */
    private $_output = null;

    public function __construct(OutputInterface $output, $max = 0)
    {
        $this->_output = $output;
        #parent::__construct($output, $max);
    }

    public function clear() {}
    public function advance($step = 1) {}
    public function display() {}
    public function start($max = null) {}
    public function finish() {}
    public function setFormat($format) {}
    public function setMessage($message, $name = 'message')
    {
        $this->_output->writeln('['.date_format(new \DateTime(), "Y-m-d H:i:s").'] '.$message);
    }
}

class RecommenderFileLock
{
    private $lock_file_path;
    private $lock_file;
    private $output;

    public function __construct($app_root_dir, OutputInterface $output)
    {
        $this->lock_file_path = $app_root_dir . '/' . RecommenderManager::RECOMMENDER_LOCK_FILE_NAME;
        $this->lock_file = null;
        $this->output = $output;
    }

    public function lock()
    {
        $this->lock_file = fopen($this->lock_file_path, 'w+');
        $this->output->writeln('[RecommenderFileLock] Trying to acquire lock...');
        while (flock($this->lock_file, LOCK_EX) == false) {
            $this->output->writeln('[RecommenderFileLock] Waiting for file lock to be released...');
            sleep(1);
        }

        $this->output->writeln('[RecommenderFileLock] Lock acquired...');
        fwrite($this->lock_file, 'User similarity computation in progress...');
    }

    public function unlock()
    {
        if ($this->lock_file == null) {
            return;
        }

        $this->output->writeln('[RecommenderFileLock] Lock released...');
        flock($this->lock_file, LOCK_UN);
        fclose($this->lock_file);
        @unlink($this->lock_file_path);
    }
}
