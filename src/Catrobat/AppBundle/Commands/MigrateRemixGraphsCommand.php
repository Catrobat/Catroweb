<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Entity\RemixManager;
use Catrobat\AppBundle\Listeners\RemixUpdater;
use Catrobat\AppBundle\Services\CatrobatFileExtractor;
use Catrobat\AppBundle\Services\RemixData;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Services\AsyncHttpClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Entity\UserManager;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Console\Helper\ProgressBar;


class MigrateRemixGraphsCommand extends ContainerAwareCommand
{
  /**
   * @var Filesystem
   */
  private $file_system;

  /**
   * @var AsyncHttpClient
   */
  private $async_http_client;

  /**
   * @var UserManager
   */
  private $user_manager;

  /**
   * @var ProgramManager
   */
  private $program_manager;

  /**
   * @var RemixManager
   */
  private $remix_manager;

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
   * @var MigrationFileLock
   */
  private $migration_file_lock;

  public function __construct(Filesystem $filesystem, UserManager $user_manager,
                              ProgramManager $program_manager, RemixManager $remix_manager,
                              EntityManager $entity_manager, $app_root_dir)
  {
    parent::__construct();
    $this->file_system = $filesystem;
    $this->async_http_client = new AsyncHttpClient(['timeout' => 12, 'max_number_of_concurrent_requests' => 10]);
    $this->user_manager = $user_manager;
    $this->program_manager = $program_manager;
    $this->remix_manager = $remix_manager;
    $this->entity_manager = $entity_manager;
    $this->app_root_dir = $app_root_dir;
    $this->output = null;
    $this->migration_file_lock = null;
  }

  protected function configure()
  {
    $this->setName('catrobat:remixgraph:migrate')
      ->setDescription('Imports remix graphs from all XML files of uploaded programs to database')
      ->addArgument('directory', InputArgument::REQUIRED, 'Directory containing catrobat files for import')
      ->addArgument('user', InputArgument::OPTIONAL, 'User who will be the owner of these programs ' .
        '(only required if --debug-import-missing-programs is set)')
      ->addOption('debug-import-missing-programs', InputOption::VALUE_OPTIONAL);
  }

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

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    declare(ticks=1);
    $this->migration_file_lock = new MigrationFileLock($this->app_root_dir, $output);
    $this->output = $output;
    pcntl_signal(SIGTERM, [$this, 'signalHandler']);
    pcntl_signal(SIGHUP, [$this, 'signalHandler']);
    pcntl_signal(SIGINT, [$this, 'signalHandler']);
    pcntl_signal(SIGUSR1, [$this, 'signalHandler']);

    $directory = $input->getArgument('directory');
    $is_debug_import_missing_programs = $input->getOption('debug-import-missing-programs');

    if (!is_dir($directory))
    {
      $output->writeln("Given directory does not exist!");

      return;
    }

    $directory = (substr($directory, -1) != '/') ? $directory . '/' : $directory;

    if ($is_debug_import_missing_programs)
    {
      $username = $input->getArgument('user');
      $this->debugImportMissingPrograms($output, $directory, $username);
    }

    $this->migrateRemixDataOfExistingPrograms($output, $directory);
  }

  private function migrateRemixDataOfExistingPrograms(OutputInterface $output, $directory)
  {
    $migration_start_time = new \DateTime();
    $progress_bar_format_simple = '%current%/%max% [%bar%] %percent:3s%% | Elapsed: %elapsed:6s% | Status: %message%';
    $progress_bar_format_verbose = '%current%/%max% [%bar%] %percent:3s%% | Elapsed: %elapsed:6s% | ' .
      'ETA: %estimated:-6s% | Status: %message%';

    //==============================================================================================================
    // (1) lock
    //==============================================================================================================
    $this->migration_file_lock->lock();

    //==============================================================================================================
    // (2) remove all existing remix relations
    //==============================================================================================================
    $this->remix_manager->removeAllRelations();
    $this->entity_manager->clear();
    $this->program_manager->markAllProgramsAsNotYetMigrated();
    $this->entity_manager->clear();

    //==============================================================================================================
    // (3) create remix relations with parents that have already been visited by previous loop iterations!!
    //==============================================================================================================
    $total_number_of_existing_programs = count($this->program_manager->findAll());
    $progress_bar = new ProgressBar($output, $total_number_of_existing_programs);
    $progress_bar->setFormat($progress_bar_format_verbose);
    $progress_bar->start();

    $skipped = 0;
    $previous_program_id = 0;
    $remix_data_map = [];

    while (($program_id = $this->program_manager->findNext($previous_program_id)) != null)
    {
      $program_file_path = $directory . $program_id . '.catrobat';
      $program = $this->program_manager->find($program_id);
      assert($program != null);
      $truncated_program_name = mb_strimwidth($program->getName(), 0, 12, "...");

      $result = $this->extractRemixData($program_file_path, $program_id, $truncated_program_name, $output, $progress_bar);
      if ($result['languageVersion'] == '0.0')
      {
        ++$skipped;
      }

      $progress_bar->setMessage('Migrating forward remixes of "' . $truncated_program_name . '" (#' . $program_id . ')');
      $remix_data_map[$program_id] = $result['fullRemixData'];
      $this->addRemixData($program, $result['remixDataOnlyForwardParents'], false);

      $progress_bar->clear();
      $output->writeln('Migrated forward remix data of "' . $truncated_program_name . '" (#' . $program_id . ')');
      $progress_bar->advance();
      $progress_bar->display();
      $previous_program_id = $program_id;
    }

    $duration = (new \DateTime())->getTimestamp() - $migration_start_time->getTimestamp();
    $progress_bar->setMessage('');
    $progress_bar->finish();
    $output->writeln('');
    $output->writeln('<info>Migrated only forward remixes of ' . count($remix_data_map) .
      ' programs (Skipped ' . $skipped . ') Duration: ' . $duration . '</info>');

    //==============================================================================================================
    // (4) now, all programs have been visited by the foreach-loop above:
    //     -> perform update with full remix data -> automatically creates remix relations with missing parents!!
    //==============================================================================================================
    $progress_bar = new ProgressBar($output, count($remix_data_map));
    $progress_bar->setFormat($progress_bar_format_verbose);
    $progress_bar->start();

    $all_program_ids = array_keys($remix_data_map);
    sort($all_program_ids);

    foreach ($all_program_ids as $program_id)
    {
      $program = $this->program_manager->find($program_id);
      $truncated_program_name = mb_strimwidth($program->getName(), 0, 12, "...");

      $progress_bar->setMessage('Migrating remaining remixes of "' . $truncated_program_name . '" (#' . $program_id . ')');
      $this->addRemixData($program, $remix_data_map[$program_id], true);

      $progress_bar->clear();
      $output->writeln('Migrated remaining remixes of "' . $truncated_program_name . '" (#' . $program_id . ')');
      $progress_bar->advance();
      $progress_bar->display();
    }

    $duration = (new \DateTime())->getTimestamp() - $migration_start_time->getTimestamp();
    $progress_bar->setMessage('');
    $progress_bar->finish();
    $output->writeln('');
    $output->writeln('<info>Migrated remaining remixes of ' . count($remix_data_map) .
      ' programs (Skipped ' . $skipped . ') Duration: ' . $duration . '</info>');

    //==============================================================================================================
    // (5) migrate remix data of all programs that have been uploaded by users during migration!
    //==============================================================================================================
    $progress_bar = new ProgressBar($output);
    $progress_bar->setFormat($progress_bar_format_simple);
    $progress_bar->start();
    $intermediate_uploads = 0;
    $skipped = 0;

    while (($unmigrated_program = $this->program_manager->findOneByRemixMigratedAt(null)) != null)
    {
      $program_file_path = $directory . $program_id . '/';
      $program_id = $unmigrated_program->getId();
      $truncated_program_name = mb_strimwidth($unmigrated_program->getName(), 0, 12, "...");

      $result = $this->extractRemixData($program_file_path, $program_id, $unmigrated_program->getName(), $output, $progress_bar);
      if ($result['languageVersion'] == '0.0')
      {
        ++$skipped;
      }

      $progress_bar->setMessage('Migrating all remixes of "' . $truncated_program_name . '" (#' . $program_id .
        ') that has been uploaded in the meantime');
      $this->addRemixData($unmigrated_program, $result['fullRemixData'], true);

      $progress_bar->clear();
      $output->writeln('Migrated all remixes of "' . $truncated_program_name . '" (#' . $program_id . ')');
      $progress_bar->advance();
      $progress_bar->display();
      ++$intermediate_uploads;
    }

    $duration = (new \DateTime())->getTimestamp() - $migration_start_time->getTimestamp();
    $progress_bar->setMessage('');
    $progress_bar->finish();
    $output->writeln('');
    $output->writeln('<info>Migrated remixes of ' . $intermediate_uploads . ' programs uploaded ' .
      'during migration (Skipped ' . $skipped . ') Duration: ' . $duration . '</info>');

    //==============================================================================================================
    // (6) unlock
    //==============================================================================================================
    $this->migration_file_lock->unlock();

    //==============================================================================================================
    // (7) finally mark all relations as seen, so the users will not get bothered with many remix user notifications
    //==============================================================================================================
    $seen_at = new \DateTime();
    $seen_at->setTimestamp(0); // 1970-01-01 in order to indicate that this was not seen by the user
    $this->remix_manager->markAllUnseenRemixRelationsAsSeen($seen_at);
  }

  private function extractRemixData($program_file_path, $program_id, $program_name, OutputInterface $output, ProgressBar $progress_bar)
  {
    /** @var CatrobatFileExtractor $file_extractor */
    $file_extractor = $this->getContainer()->get('fileextractor');
    $extracted_file = null;

    $progress_bar->setMessage('Extracting XML of program #' . $program_id . ' "' . $program_name . '"');

    try
    {
      $program_file = new File($program_file_path);
      //$extracted_file = new ExtractedCatrobatFile($program_file_path, $program_file_path, null);
      $extracted_file = $file_extractor->extract($program_file);
    } catch (\Exception $ex)
    {
      $progress_bar->clear();
      $output->writeln('<error>Cannot find Catrobat file of Program #' . $program_id .
        ', path of Catrobat file: ' . $program_file_path . '</error>');
      $progress_bar->display();
    }

    $empty_result = ['remixDataOnlyForwardParents' => [], 'fullRemixData' => [], 'languageVersion' => '0.0'];
    $result = $empty_result;

    if ($extracted_file != null)
    {
      //----------------------------------------------------------------------------------------------------------
      // NOTE: this is a workaround only needed for migration purposes in order to stay backward compatible
      //       with older XML files -> do not change order here
      //----------------------------------------------------------------------------------------------------------
      $url_data = $extracted_file->getRemixesData(PHP_INT_MAX, true, false);
      assert(count($url_data) == 1, 'WTH! This program has multiple urls with different program IDs?!!');
      assert($url_data[0]->getProgramId() == $program_id);

      //$remix_of_string = $extracted_file->getRemixMigrationUrlsString();
      $remix_data_only_forward_parents = $extracted_file->getRemixesData($program_id, true, true);
      $full_remix_data = $extracted_file->getRemixesData($program_id, false, true);
      $language_version = $extracted_file->getLanguageVersion();

      $result = [
        'remixDataOnlyForwardParents' => $remix_data_only_forward_parents,
        'fullRemixData'               => $full_remix_data,
        'languageVersion'             => $language_version,
      ];
    }

    // ignore remix parents of old Catrobat programs, Catroid had a bug until Catrobat Language Version 0.992
    // For more details on this, please have a look at: https://jira.catrob.at/browse/CAT-2149
    if (version_compare($result['languageVersion'], '0.992', '<=') && (count($result['fullRemixData']) >= 2))
    {
      $progress_bar->clear();
      $output->writeln('<error>Could not migrate remixes of MERGED program ' . $program_id .
        ' - version too old: ' . $result['languageVersion'] . '</error>');
      $progress_bar->display();
      $result = $empty_result;
    }

    return $result;
  }

  private function addRemixData(Program $program, array $remixes_data, $is_update = false)
  {
    assert($program != null);
    $scratch_remixes_data = array_filter($remixes_data, function ($remix_data) {
      /** @var RemixData $remix_data */
      return $remix_data->isScratchProgram();
    });
    $scratch_info_data = [];

    if (count($scratch_remixes_data) > 0)
    {
      $scratch_ids = array_map(function ($data) {
        return $data->getProgramId();
      }, $scratch_remixes_data);
      $existing_scratch_ids = $this->remix_manager->filterExistingScratchProgramIds($scratch_ids);
      $not_existing_scratch_ids = array_diff($scratch_ids, $existing_scratch_ids);
      $scratch_info_data = $this->async_http_client->fetchScratchProgramDetails($not_existing_scratch_ids);
    }

    $preserved_version = $program->getVersion();
    $program->setVersion($is_update ? (Program::INITIAL_VERSION + 1) : Program::INITIAL_VERSION);

    $this->remix_manager->addScratchPrograms($scratch_info_data);
    $this->remix_manager->addRemixes($program, $remixes_data);

    $program->setVersion($preserved_version);
    $this->entity_manager->persist($program);
    $this->entity_manager->flush();
    $this->entity_manager->clear();
  }

  private function debugImportMissingPrograms(OutputInterface $output, $directory, $username)
  {
    $finder = new Finder();
    $finder->files()->name('*.catrobat')->in($directory)->depth(0);

    if ($finder->count() == 0)
    {
      $output->writeln('No catrobat files found');

      return;
    }

    $user = $this->user_manager->findUserByUsername($username);
    if ($user == null)
    {
      $output->writeln('User "' . $username . '" was not found! You must pass a valid username ' .
        'as the user argument in order to use --debug-import-missing-programs!');

      return;
    }

    $skipped = 0;
    $progress_bar = new ProgressBar($output, $finder->count());
    $progress_bar->setFormat(' %current%/%max% [%bar%] %message%');
    $progress_bar->start();
    $number_imported_programs = 0;

    $metadata = $this->entity_manager->getClassMetaData("Catrobat\AppBundle\Entity\Program");
    $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

    $batch_size = 300;

    foreach ($finder as $program_file_path)
    {
      /** @var CatrobatFileExtractor $fileextractor */
      $fileextractor = $this->getContainer()->get('fileextractor');
      $program_file = new File($program_file_path);
      $extracted_file = $fileextractor->extract($program_file);

      $url_string = $extracted_file->getRemixUrlsString();
      $original_program_data = new RemixData($url_string);
      $program_id = $original_program_data->getProgramId();

      $progress_bar->setMessage('Importing program ' . $extracted_file->getName() . ' (#' . $program_id . ')');
      $progress_bar->advance();

      if ($this->program_manager->find($program_id) != null)
      {
        ++$skipped;
        continue;
      }

      $language_version = $extracted_file->getLanguageVersion();

      // ignore old programs except for manually changed ones - because FU
      if (version_compare($language_version, '0.8', '<') && $program_id != 821)
      {
        $progress_bar->clear();
        $output->writeln('<error>Could not import program ' . $program_id . ' - version too old: ' . $language_version . '</error>');
        $progress_bar->display();
        ++$skipped;
        continue;
      }

      $program = new Program();
      $program->setId($program_id);
      $program->setName($extracted_file->getName());
      $program->setDescription($extracted_file->getDescription());
      $program->setUploadIp('127.0.0.1');
      $program->setDownloads(0);
      $program->setViews(0);
      $program->setVisible(true);
      $program->setUser($user);
      $program->setUploadLanguage('en');
      $program->setUploadedAt(new \DateTime());
      $program->setRemixMigratedAt(null);
      $program->setFilesize($program_file->getSize());
      $program->setCatrobatVersion(1);
      $program->setCatrobatVersionName($extracted_file->getApplicationVersion());

      if ($program_id == 821)
      {
        $program->setLanguageVersion('0.8');
      }
      else
      {
        $program->setLanguageVersion($language_version);
      }

      $program->setApproved(true);
      $program->setCatrobatVersion(1);
      $program->setFlavor('pocketcode');
      $program->setRemixRoot(true);

      $this->entity_manager->persist($program);
      if (($number_imported_programs % $batch_size) === 0)
      {
        $this->entity_manager->flush();
        $this->entity_manager->detach($program);
      }

      $progress_bar->setMessage('Added program "' . $program->getName() . '" (#' . $program_id . ')');
      ++$number_imported_programs;
    }

    $progress_bar->setMessage('Saving to database');
    $this->entity_manager->flush();
    $progress_bar->setMessage('');
    $progress_bar->finish();
    $output->writeln('');
    $output->writeln('<info>Imported ' . $number_imported_programs . ' programs (Skipped ' . $skipped . ')</info>');
  }
}

class MigrationFileLock
{
  private $lock_file_path;
  private $lock_file;
  private $output;

  public function __construct($app_root_dir, OutputInterface $output)
  {
    $this->lock_file_path = $app_root_dir . '/' . RemixUpdater::MIGRATION_LOCK_FILE_NAME;
    $this->lock_file = null;
    $this->output = $output;
  }

  public function lock()
  {
    $this->lock_file = fopen($this->lock_file_path, 'w+');
    $this->output->writeln('[MigrationFileLock] Trying to acquire lock...');
    while (flock($this->lock_file, LOCK_EX) == false)
    {
      $this->output->writeln('[MigrationFileLock] Waiting for file lock to be released...');
      sleep(1);
    }

    $this->output->writeln('[MigrationFileLock] Lock acquired...');
    fwrite($this->lock_file, 'Migration of remixes in progress...');
  }

  public function unlock()
  {
    if ($this->lock_file == null)
    {
      return;
    }

    $this->output->writeln('[MigrationFileLock] Lock released...');
    flock($this->lock_file, LOCK_UN);
    fclose($this->lock_file);
    @unlink($this->lock_file_path);
  }
}
