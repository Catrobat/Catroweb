<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\CommandHelper;
use App\Catrobat\Listeners\RemixUpdater;
use App\Catrobat\Services\CatrobatFileExtractor;
use App\Catrobat\Services\ProgramFileRepository;
use App\Catrobat\Services\ScreenshotRepository;
use App\Entity\FeaturedProgram;
use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\RemixManager;
use App\Entity\User;
use App\Entity\UserManager;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ImportLegacyCommand.
 */
class ImportLegacyCommand extends Command
{
  const RESOURCE_CONTAINER_FILE = 'resources.tar';

  const SQL_CONTAINER_FILE = 'sql.tar';

  const SQL_WEB_CONTAINER_FILE = 'catroweb-sql.tar.gz';

  const TSV_USERS_FILE = '2034.dat';

  const TSV_PROGRAMS_FILE = '2041.dat';

  const TSV_FEATURED_PROGRAMS = '2037.dat';

  private Filesystem $file_system;

  private UserManager $user_manager;

  private ProgramManager $program_manager;

  private RemixManager $remix_manager;

  private OutputInterface $output;

  private EntityManagerInterface $em;

  /**
   * @var
   */
  private $importdir;
  /**
   * @var
   */
  private $finder;

  private ScreenshotRepository $screenshot_repository;

  private ProgramFileRepository $catrobat_file_repository;

  private RouterInterface $router;

  private CatrobatFileExtractor $file_extractor;

  private RemixUpdater $remix_updater;

  /**
   * ImportLegacyCommand constructor.
   */
  public function __construct(Filesystem $file_system, UserManager $user_manager, ProgramManager $program_manager,
                              RemixManager $remix_manager, EntityManagerInterface $em,
                              ScreenshotRepository $screenshot_repository, ProgramFileRepository $file_repository,
                              CatrobatFileExtractor $catrobat_file_extractor, RouterInterface $router,
                              RemixUpdater $remix_updater)
  {
    parent::__construct();
    $this->file_system = $file_system;
    $this->user_manager = $user_manager;
    $this->program_manager = $program_manager;
    $this->remix_manager = $remix_manager;
    $this->em = $em;
    $this->screenshot_repository = $screenshot_repository;
    $this->catrobat_file_repository = $file_repository;
    $this->router = $router;
    $this->file_extractor = $catrobat_file_extractor;
    $this->remix_updater = $remix_updater;
  }

  protected function configure()
  {
    $this->setName('catrobat:legacy:import')
      ->setDescription('Import a legacy backup')
      ->addArgument('backupfile', InputArgument::REQUIRED, 'legacy backup file (tar.gz)')
    ;
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): void
  {
    $this->output = $output;
    $this->file_system = new Filesystem();
    $this->finder = new Finder();

    CommandHelper::executeSymfonyCommand('catrobat:purge', $this->getApplication(), ['--force' => true], $output);

    $backup_file = $input->getArgument('backupfile');

    $this->importdir = $this->createTempDir();
    $this->writeln('Using Temp directory '.$this->importdir);

    $temp_dir = $this->importdir;
    CommandHelper::executeShellCommand(
      ['tar', 'xfz', $backup_file, '--directory', $temp_dir], ['timeout' => 3600],
      'Extracting backupfile', $output
    );
    CommandHelper::executeShellCommand(
      ['tar', 'xf', $temp_dir.'/'.self::SQL_CONTAINER_FILE, '--directory', $temp_dir], ['timeout' => 3600],
      'Extracting SQL files', $output
  );
    CommandHelper::executeShellCommand(
      ['tar', 'xf', $temp_dir.'/'.self::SQL_WEB_CONTAINER_FILE, '--directory', $temp_dir], ['timeout' => 3600],
      'Extracting Catroweb SQL files', $output
  );
    CommandHelper::executeShellCommand(
      ['tar', 'xf', $temp_dir.'/'.self::RESOURCE_CONTAINER_FILE, '--directory', $temp_dir], ['timeout' => 3600],
      'Extracting resource files', $output
  );

    $this->importUsers($this->importdir.'/'.self::TSV_USERS_FILE);
    $this->importPrograms($this->importdir.'/'.self::TSV_PROGRAMS_FILE);
    $this->importProgramFiles($this->importdir.'/'.self::TSV_PROGRAMS_FILE);

    $row = 0;
    $features_tsv = $this->importdir.'/'.self::TSV_FEATURED_PROGRAMS;
    if (false !== ($handle = fopen($features_tsv, 'r')))
    {
      while (false !== ($data = fgetcsv($handle, 0, "\t")))
      {
        $num = count($data);
        if ($num > 2)
        {
          $featured_program = new FeaturedProgram();
          /** @var Program $project */
          $project = $this->program_manager->find($data[1]);
          $featured_program->setProgram($project);
          $featured_program->setActive('t' === $data[3]);
          $featured_program->setNewFeaturedImage(new File($this->importdir.'/resources/featured/'.$data[1].'.jpg'));
          $this->em->persist($featured_program);
        }
        else
        {
          break;
        }
        ++$row;
      }
      $this->em->flush();
      fclose($handle);
      $this->writeln('Imported '.$row.' featured programs');
    }

    $this->file_system->remove($temp_dir);
  }

  /**
   * @param $program_file
   *
   * @throws Exception
   */
  protected function importPrograms($program_file)
  {
    $row = 0;
    $skipped = 0;

    $progress = new ProgressBar($this->output);
    $progress->setFormat(' %current%/%max% [%bar%] %message%');
    $progress->start();

    $metadata = $this->em->getClassMetaData(Program::class);
    $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

    if (false !== ($handle = fopen($program_file, 'r')))
    {
      while (false !== ($data = fgetcsv($handle, 0, "\t")))
      {
        $num = count($data);
        if ($num > 2)
        {
          $id = $data[0];
          $language_version = $data[13];

          $progress->setMessage($data[1].' ('.$id.')');
          $progress->advance();

          // ignore old programs except for manually changed ones - because FU
          if (version_compare($language_version, '0.8', '<') && 821 != $id)
          {
            $progress->clear();
            $this->writeln('<error>Could not import program '.$id.' - version too old: '.$language_version.'</error>');
            $progress->display();
            ++$skipped;
            continue;
          }
          $program = new Program();
          $program->setId($id);
          $program->setName($data[1]);
          $description = $data[2];
          $description = str_replace('<br />\\n', "\n", $description);
          $program->setDescription($description);
          $credits = "No credits available.\n";
          $program->setCredits($credits);
          $program->setUploadedAt(new DateTime($data[4], new DateTimeZone('UTC')));
          $program->setUploadIp($data[5]);
          $program->setRemixMigratedAt(null);
          $program->setDownloads($data[6]);
          $program->setViews($data[7]);
          $program->setVisible('t' === $data[8]);

          /** @var User $user */
          $user = $this->user_manager->find($data[9]);
          $program->setUser($user);
          $program->setUploadLanguage($data[10]);
          $program->setFilesize($data[11]);
          $program->setCatrobatVersionName($data[12]);

          if (821 == $id)
          {
            $program->setLanguageVersion('0.8');
          }

          $program->setLanguageVersion($language_version);

          $program->setApproved('t' === $data[20]);
          $program->setCatrobatVersion(1);
          $program->setFlavor('pocketcode');
          $program->setRemixRoot(true);
          $this->em->persist($program);
        }
        else
        {
          break;
        }
        ++$row;
      }
      fclose($handle);

      $progress->setMessage('Saving to database');
      $progress->advance();
      $this->em->flush();
      $progress->setMessage('');
      $progress->finish();
      $this->writeln('');
      $this->writeln('<info>Imported '.$row.' programs (Skipped '.$skipped.')</info>');
    }
  }

  /**
   * @param $program_file
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  protected function importProgramFiles($program_file)
  {
    $row = 0;
    $skipped = 0;

    $progress = new ProgressBar($this->output);
    $progress->setFormat(' %current%/%max% [%bar%] %message%');
    $progress->start();

    $metadata = $this->em->getClassMetaData('App\\Entity\\Program');
    $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

    if (false !== ($handle = fopen($program_file, 'r')))
    {
      while (false !== ($data = fgetcsv($handle, 0, "\t")))
      {
        $num = count($data);
        if ($num > 2)
        {
          $id = $data[0];
          $language_version = $data[13];

          $progress->setMessage($data[1].' ('.$id.')');
          $progress->advance();

          if (version_compare($language_version, '0.8', '<') && 821 != $id)
          {
            ++$skipped;
            continue;
          }
          $this->importScreenshots($id);
          $this->importProgramfile($id);
        }
        else
        {
          break;
        }
        ++$row;
      }
      fclose($handle);

      $progress->setMessage('Saving to database');
      $progress->advance();
      $this->em->flush();
      $progress->setMessage('');
      $progress->finish();
      $this->writeln('');
      $this->writeln('<info>Imported '.$row.' programs (Skipped '.$skipped.')</info>');
    }
  }

  /**
   * @param $user_file
   */
  protected function importUsers($user_file)
  {
    print_r($user_file);

    $row = 0;

    $progress = new ProgressBar($this->output);
    $progress->setFormat(' %current%/%max% [%bar%] %message%');
    $progress->start();

    $metadata = $this->em->getClassMetaData('App\\Entity\\User');
    $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

    if (false !== ($handle = fopen($user_file, 'r')))
    {
      while (false !== ($data = fgetcsv($handle, 0, "\t")))
      {
        $num = count($data);
        if ($num > 2)
        {
          // Special case - same email on two accounts, this one has no programs
          if ('paul70078' == $data[1])
          {
            continue;
          }
          // Special case - no id 0
          if (0 == $data[0])
          {
            continue;
          }

          $progress->setMessage($data[1].' ('.$data[0].')');
          $progress->advance();

          $user = new User();
          $user->setId($data[0]);
          $user->setUsername($data[1]);
          $user->setPassword($data[2]);
          $user->setEmail($data[3]);
          $user->setCountry(strtoupper($data[4]));
          $user->setUploadToken($data[11]);
          $user->setEnabled(true);
          $user->setAvatar(('\\N' === $data[13]) ? null : $data[13]);
          $user->setAdditionalEmail('\\N' === $data[14] ? null : $data[14]);
          $this->em->persist($user);
        }
        else
        {
          break;
        }
        ++$row;
      }
      fclose($handle);
      $progress->setMessage('Saving to database');
      $progress->advance();
      $this->em->flush();
      $progress->setMessage('');
      $progress->finish();
      $this->writeln('');
      $this->writeln('<info>Imported '.$row.' users</info>');
    }
  }

  /**
   * @param $id
   */
  private function importScreenshots($id)
  {
    $screenhot_dir = $this->importdir.'/resources/thumbnails/';
    $screenshot_path = $screenhot_dir.$id.'_large.png';
    $thumbnail_path = $screenhot_dir.$id.'_small.png';
    if (file_exists($screenshot_path))
    {
      $this->screenshot_repository->importProgramAssets($screenshot_path, $thumbnail_path, $id);
    }
  }

  /**
   * @param $id
   *
   * @throws ORMException
   * @throws OptimisticLockException
   */
  private function importProgramfile($id)
  {
    $filepath = $this->importdir.'/resources/projects/'."{$id}".'.catrobat';

    if (file_exists($filepath))
    {
      $extracted_catrobat_file = $this->file_extractor->extract(new File($filepath));

      /** @var Program $program */
      $program = $this->program_manager->find($id);

      $this->remix_updater->update($extracted_catrobat_file, $program);

      $this->catrobat_file_repository->saveProgram($extracted_catrobat_file, $id);
      $this->catrobat_file_repository->saveProgramfile(new File($filepath), $id);
    }
  }

  private function writeln(string $string)
  {
    if (null != $this->output)
    {
      $this->output->writeln($string);
    }
  }

  /**
   * @throws Exception
   */
  private function createTempDir(): string
  {
    $temp_file = tempnam(sys_get_temp_dir(), 'catimport');
    if (file_exists($temp_file))
    {
      unlink($temp_file);
    }
    mkdir($temp_file);
    if (is_dir($temp_file))
    {
      return $temp_file;
    }
    throw new Exception('Dir creation failed');
  }
}
