<?php

namespace App\System\Commands;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProgramRepository;
use App\Project\CatrobatFile\CatrobatFileExtractor;
use App\Project\ProjectManager;
use App\Project\Remix\RemixData;
use App\Project\Remix\RemixManager;
use App\Project\Scratch\AsyncHttpClient;
use App\System\Commands\Helpers\MigrationFileLock;
use App\User\UserManager;
use App\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;

class MigrateRemixGraphsCommand extends Command
{
  private readonly AsyncHttpClient $async_http_client;

  private readonly string $app_root_dir;

  private ?OutputInterface $output = null;

  private ?MigrationFileLock $migration_file_lock = null;

  public function __construct(private readonly UserManager $user_manager,
    private readonly ProjectManager $project_manager, private readonly RemixManager $remix_manager,
    private readonly EntityManagerInterface $entity_manager, private readonly CatrobatFileExtractor $file_extractor,
    private readonly ProgramRepository $project_repository, ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->async_http_client = new AsyncHttpClient(['timeout' => 12, 'max_number_of_concurrent_requests' => 10]);
    $this->app_root_dir = (string) $parameter_bag->get('kernel.project_dir');
  }

  public function signalHandler(int $signal_number): void
  {
    $this->output->writeln('[SignalHandler] Called Signal Handler');
    match ($signal_number) {
      SIGTERM => $this->output->writeln('[SignalHandler] User aborted the process'),
      SIGHUP => $this->output->writeln('[SignalHandler] SigHup detected'),
      SIGINT => $this->output->writeln('[SignalHandler] SigInt detected'),
      SIGUSR1 => $this->output->writeln('[SignalHandler] SigUsr1 detected'),
      default => $this->output->writeln('[SignalHandler] Signal '.$signal_number.' detected'),
    };

    $this->migration_file_lock->unlock();
    exit(-1);
  }

  protected function configure(): void
  {
    $this->setName('catrobat:remixgraph:migrate')
      ->setDescription('Imports remix graphs from all XML files of uploaded programs to database')
      ->addArgument('directory', InputArgument::REQUIRED, 'Directory containing catrobat files for import')
      ->addArgument('user', InputArgument::OPTIONAL, 'User who will be the owner of these programs '.
        '(only required if --debug-import-missing-programs is set)')
      ->addOption('debug-import-missing-programs', 'debug')
    ;
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    declare(ticks=1);
    $this->migration_file_lock = new MigrationFileLock($this->app_root_dir, $output);
    $this->output = $output;
    pcntl_signal(SIGTERM, $this->signalHandler(...));
    pcntl_signal(SIGHUP, $this->signalHandler(...));
    pcntl_signal(SIGINT, $this->signalHandler(...));
    pcntl_signal(SIGUSR1, $this->signalHandler(...));

    $directory = $input->getArgument('directory');
    $is_debug_import_missing_projects = $input->getOption('debug-import-missing-programs');

    if (!is_dir($directory)) {
      $output->writeln('Given directory does not exist!');

      return 2;
    }

    $directory = ('/' != substr((string) $directory, -1)) ? $directory.'/' : $directory;

    if ($is_debug_import_missing_projects) {
      $username = $input->getArgument('user');
      $this->debugImportMissingProjects($output, $directory, $username);
    }

    $this->migrateRemixDataOfExistingProjects($output, $directory);

    return 0;
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   */
  private function migrateRemixDataOfExistingProjects(OutputInterface $output, string $directory): void
  {
    /* @var Program $unmigrated_project */

    $migration_start_time = TimeUtils::getDateTime();
    $progress_bar_format_simple = '%current%/%max% [%bar%] %percent:3s%% | Elapsed: %elapsed:6s% | Status: %message%';
    $progress_bar_format_verbose = '%current%/%max% [%bar%] %percent:3s%% | Elapsed: %elapsed:6s% | '.
      'ETA: %estimated:-6s% | Status: %message%';

    // ==============================================================================================================
    // (1) lock
    // ==============================================================================================================
    $this->migration_file_lock->lock();

    // ==============================================================================================================
    // (2) remove all existing remix relations
    // ==============================================================================================================
    $this->remix_manager->removeAllRelations();
    $this->entity_manager->clear();
    $this->project_manager->markAllProjectsAsNotYetMigrated();
    $this->entity_manager->clear();

    // ==============================================================================================================
    // (3) create remix relations with parents that have already been visited by previous loop iterations!!
    // ==============================================================================================================
    $total_number_of_existing_projects = count($this->project_manager->findAll());
    $progress_bar = new ProgressBar($output, $total_number_of_existing_projects);
    $progress_bar->setFormat($progress_bar_format_verbose);
    $progress_bar->start();

    $skipped = 0;
    $previous_project_id = '0';
    $remix_data_map = [];

    while (null != ($project_id = $this->project_manager->findNext($previous_project_id))) {
      $project_file_path = $directory.$project_id.'.catrobat';

      $project = $this->project_manager->find($project_id);
      assert(null != $project);
      $truncated_project_name = mb_strimwidth((string) $project->getName(), 0, 12, '...');

      $result = $this->extractRemixData($project_file_path, $project_id, $truncated_project_name, $output, $progress_bar);
      if ('0.0' == $result['languageVersion']) {
        ++$skipped;
      }

      $progress_bar->setMessage('Migrating forward remixes of "'.$truncated_project_name.'" (#'.$project_id.')');
      $remix_data_map[$project_id] = $result['fullRemixData'];
      $this->addRemixData($project, $result['remixDataOnlyForwardParents'], false);

      $progress_bar->clear();
      $output->writeln('Migrated forward remix data of "'.$truncated_project_name.'" (#'.$project_id.')');
      $progress_bar->advance();
      $progress_bar->display();
      $previous_project_id = $project_id;
    }

    $duration = TimeUtils::getDateTime()->getTimestamp() - $migration_start_time->getTimestamp();
    $progress_bar->setMessage('');
    $progress_bar->finish();
    $output->writeln('');
    $output->writeln('<info>Migrated only forward remixes of '.count($remix_data_map).
      ' projects (Skipped '.$skipped.') Duration: '.$duration.'</info>');

    // ==============================================================================================================
    // (4) now, all projects have been visited by the foreach-loop above:
    //     -> perform update with full remix data -> automatically creates remix relations with missing parents!!
    // ==============================================================================================================
    $progress_bar = new ProgressBar($output, count($remix_data_map));
    $progress_bar->setFormat($progress_bar_format_verbose);
    $progress_bar->start();

    $all_project_ids = array_keys($remix_data_map);
    sort($all_project_ids);

    foreach ($all_project_ids as $project_id) {
      $project = $this->project_manager->find($project_id);
      $truncated_project_name = mb_strimwidth((string) $project->getName(), 0, 12, '...');

      $progress_bar->setMessage('Migrating remaining remixes of "'.$truncated_project_name.'" (#'.$project_id.')');
      $this->addRemixData($project, $remix_data_map[$project_id], true);

      $progress_bar->clear();
      $output->writeln('Migrated remaining remixes of "'.$truncated_project_name.'" (#'.$project_id.')');
      $progress_bar->advance();
      $progress_bar->display();
    }

    $duration = TimeUtils::getDateTime()->getTimestamp() - $migration_start_time->getTimestamp();
    $progress_bar->setMessage('');
    $progress_bar->finish();
    $output->writeln('');
    $output->writeln('<info>Migrated remaining remixes of '.count($remix_data_map).
      ' projects (Skipped '.$skipped.') Duration: '.$duration.'</info>');

    // ==============================================================================================================
    // (5) migrate remix data of all projects that have been uploaded by users during migration!
    // ==============================================================================================================
    $progress_bar = new ProgressBar($output);
    $progress_bar->setFormat($progress_bar_format_simple);
    $progress_bar->start();
    $intermediate_uploads = 0;
    $skipped = 0;

    while (null != ($unmigrated_project = $this->project_manager->findOneByRemixMigratedAt(null))) {
      $project_file_path = $directory.$project_id.'/';
      $project_id = $unmigrated_project->getId();
      $truncated_project_name = mb_strimwidth((string) $unmigrated_project->getName(), 0, 12, '...');

      $result = $this->extractRemixData($project_file_path, $project_id, $unmigrated_project->getName(), $output, $progress_bar);
      if ('0.0' == $result['languageVersion']) {
        ++$skipped;
      }

      $progress_bar->setMessage('Migrating all remixes of "'.$truncated_project_name.'" (#'.$project_id.
        ') that has been uploaded in the meantime');
      $this->addRemixData($unmigrated_project, $result['fullRemixData'], true);

      $progress_bar->clear();
      $output->writeln('Migrated all remixes of "'.$truncated_project_name.'" (#'.$project_id.')');
      $progress_bar->advance();
      $progress_bar->display();
      ++$intermediate_uploads;
    }

    $duration = TimeUtils::getDateTime()->getTimestamp() - $migration_start_time->getTimestamp();
    $progress_bar->setMessage('');
    $progress_bar->finish();
    $output->writeln('');
    $output->writeln('<info>Migrated remixes of '.$intermediate_uploads.' projects uploaded '.
      'during migration (Skipped '.$skipped.') Duration: '.$duration.'</info>');

    // ==============================================================================================================
    // (6) unlock
    // ==============================================================================================================
    $this->migration_file_lock->unlock();

    // ==============================================================================================================
    // (7) finally mark all relations as seen, so the users will not get bothered with many remix user notifications
    // ==============================================================================================================
    $seen_at = TimeUtils::getDateTime();
    $seen_at->setTimestamp(0); // 1970-01-01 in order to indicate that this was not seen by the user
    $this->remix_manager->markAllUnseenRemixRelationsAsSeen($seen_at);
  }

  private function extractRemixData(mixed $project_file_path, mixed $project_id, mixed $project_name, OutputInterface $output, ProgressBar $progress_bar): array
  {
    $extracted_file = null;

    $progress_bar->setMessage('Extracting XML of project #'.$project_id.' "'.$project_name.'"');

    try {
      $project_file = new File($project_file_path);
      // $extracted_file = new ExtractedCatrobatFile($project_file_path, $project_file_path, null);
      $extracted_file = $this->file_extractor->extract($project_file);
    } catch (\Exception) {
      $progress_bar->clear();
      $output->writeln('<error>Cannot find Catrobat file of Project #'.$project_id.
        ', path of Catrobat file: '.$project_file_path.'</error>');
      $progress_bar->display();
    }

    $empty_result = ['remixDataOnlyForwardParents' => [], 'fullRemixData' => [], 'languageVersion' => '0.0'];
    $result = $empty_result;

    if (null != $extracted_file) {
      // ----------------------------------------------------------------------------------------------------------
      // NOTE: this is a workaround only needed for migration purposes in order to stay backward compatible
      //       with older XML files -> do not change order here
      // ----------------------------------------------------------------------------------------------------------
      $url_data = $extracted_file->getRemixesData('.'.PHP_INT_MAX, true, $this->project_repository, false);
      assert(1 == count($url_data), 'WTH! This project has multiple urls with different project IDs?!!');
      assert($url_data[0]->getProgramId() == $project_id);

      // $remix_of_string = $extracted_file->getRemixMigrationUrlsString();
      $remix_data_only_forward_parents = $extracted_file->getRemixesData($project_id, true, $this->project_repository, true);
      $full_remix_data = $extracted_file->getRemixesData($project_id, false, $this->project_repository, true);
      $language_version = $extracted_file->getLanguageVersion();

      $result = [
        'remixDataOnlyForwardParents' => $remix_data_only_forward_parents,
        'fullRemixData' => $full_remix_data,
        'languageVersion' => $language_version,
      ];
    }

    // ignore remix parents of old Catrobat projects, Catroid had a bug until Catrobat Language Version 0.992
    // For more details on this, please have a look at: https://jira.catrob.at/browse/CAT-2149
    if (version_compare($result['languageVersion'], '0.992', '<=') && (count($result['fullRemixData']) >= 2)) {
      $progress_bar->clear();
      $output->writeln('<error>Could not migrate remixes of MERGED project '.$project_id.
        ' - version too old: '.$result['languageVersion'].'</error>');
      $progress_bar->display();
      $result = $empty_result;
    }

    return $result;
  }

  /**
   * @throws \Exception
   */
  private function addRemixData(Program $project, array $remixes_data, bool $is_update = false): void
  {
    $scratch_remixes_data = array_filter($remixes_data, fn (RemixData $remix_data): bool => $remix_data->isScratchProject());
    $scratch_info_data = [];

    if (count($scratch_remixes_data) > 0) {
      $scratch_ids = array_map(fn (RemixData $data): string => $data->getProjectId(), $scratch_remixes_data);
      $existing_scratch_ids = $this->remix_manager->filterExistingScratchProjectIds($scratch_ids);
      $not_existing_scratch_ids = array_diff($scratch_ids, $existing_scratch_ids);
      $scratch_info_data = $this->async_http_client->fetchScratchProjectDetails($not_existing_scratch_ids);
    }

    $preserved_version = $project->getVersion();
    $project->setVersion($is_update ? (Program::INITIAL_VERSION + 1) : Program::INITIAL_VERSION);

    $this->remix_manager->addScratchProjects($scratch_info_data);
    $this->remix_manager->addRemixes($project, $remixes_data);

    $project->setVersion($preserved_version);
    $this->entity_manager->persist($project);
    $this->entity_manager->flush();
    $this->entity_manager->clear();
  }

  /**
   * @throws \Exception
   */
  private function debugImportMissingProjects(OutputInterface $output, string $directory, string $username): void
  {
    $finder = new Finder();
    $finder->files()->name('*.catrobat')->in($directory)->depth(0);

    if (0 == $finder->count()) {
      $output->writeln('No catrobat files found');

      return;
    }

    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($username);
    if (null == $user) {
      $output->writeln('User "'.$username.'" was not found! You must pass a valid username '.
        'as the user argument in order to use --debug-import-missing-programs!');

      return;
    }

    $skipped = 0;
    $progress_bar = new ProgressBar($output, $finder->count());
    $progress_bar->setFormat(' %current%/%max% [%bar%] %message%');
    $progress_bar->start();
    $number_imported_projects = 0;

    $metadata = $this->entity_manager->getClassMetaData(Program::class);
    $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

    $batch_size = 300;

    /** @var SplFileInfo $project_file_path */
    foreach ($finder as $project_file_path) {
      $project_file = new File($project_file_path->__toString());
      $extracted_file = $this->file_extractor->extract($project_file);

      $url_string = $extracted_file->getRemixUrlsString();
      $original_project_data = new RemixData($url_string);
      $project_id = $original_project_data->getProjectId();

      $progress_bar->setMessage('Importing project '.$extracted_file->getName().' (#'.$project_id.')');
      $progress_bar->advance();

      if (null != $this->project_manager->find($project_id)) {
        ++$skipped;
        continue;
      }

      $language_version = $extracted_file->getLanguageVersion();

      // ignore old projects except for manually changed ones - because FU
      if (version_compare($language_version, '0.8', '<') && 821 != $project_id) {
        $progress_bar->clear();
        $output->writeln('<error>Could not import project '.$project_id.' - version too old: '.$language_version.'</error>');
        $progress_bar->display();
        ++$skipped;
        continue;
      }

      $project = new Program();
      $project->setId($project_id);
      $project->setName($extracted_file->getName());
      $project->setDescription($extracted_file->getDescription());
      $project->setUploadIp('127.0.0.1');
      $project->setDownloads(0);
      $project->setViews(0);
      $project->setVisible(true);
      $project->setUser($user);
      $project->setUploadLanguage('en');
      $project->setUploadedAt(new \DateTime('now', new \DateTimeZone('UTC')));
      $project->setRemixMigratedAt(null);
      $project->setFilesize($project_file->getSize());
      $project->setCatrobatVersionName($extracted_file->getApplicationVersion());

      if (821 == $project_id) {
        $project->setLanguageVersion('0.8');
      } else {
        $project->setLanguageVersion($language_version);
      }

      $project->setApproved(true);
      $project->setFlavor('pocketcode');
      $project->setRemixRoot(true);

      $this->entity_manager->persist($project);
      if (0 === ($number_imported_projects % $batch_size)) {
        $this->entity_manager->flush();
        $this->entity_manager->detach($project);
      }

      $progress_bar->setMessage('Added project "'.$project->getName().'" (#'.$project_id.')');
      ++$number_imported_projects;
    }

    $progress_bar->setMessage('Saving to database');
    $this->entity_manager->flush();
    $progress_bar->setMessage('');
    $progress_bar->finish();
    $output->writeln('');
    $output->writeln('<info>Imported '.$number_imported_projects.' projects (Skipped '.$skipped.')</info>');
  }
}
