<?php

namespace App\Catrobat\Commands;

use App\Entity\RemixManager;
use App\Catrobat\Listeners\RemixUpdater;
use App\Catrobat\Services\AsyncHttpClient;
use App\Catrobat\Services\CatrobatFileExtractor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use App\Entity\ProgramManager;
use App\Entity\UserManager;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\EntityManager;
use App\Entity\Program;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use App\Entity\FeaturedProgram;
use Symfony\Component\Console\Helper\ProgressBar;
use App\Catrobat\Commands\Helpers\CommandHelper;


/**
 * Class ImportLegacyCommand
 * @package App\Catrobat\Commands
 */
class ImportLegacyCommand extends ContainerAwareCommand
{
  /**
   *
   */
  const RESOURCE_CONTAINER_FILE = 'resources.tar';
  /**
   *
   */
  const SQL_CONTAINER_FILE = 'sql.tar';
  /**
   *
   */
  const SQL_WEB_CONTAINER_FILE = 'catroweb-sql.tar.gz';
  /**
   *
   */
  const TSV_USERS_FILE = '2034.dat';
  /**
   *
   */
  const TSV_PROGRAMS_FILE = '2041.dat';
  /**
   *
   */
  const TSV_FEATURED_PROGRAMS = '2037.dat';

  /**
   * @var Filesystem
   */
  private $fileystem;
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
   * @var Output
   */
  private $output;

  /**
   * @var EntityManager
   */
  private $em;

  /**
   * @var
   */
  private $importdir;
  /**
   * @var
   */
  private $finder;
  /**
   * @var
   */
  private $filesystem;

  /**
   * @var
   */
  private $screenshot_repository;
  /**
   * @var
   */
  private $catrobat_file_repository;

  /**
   * ImportLegacyCommand constructor.
   *
   * @param Filesystem     $filesystem
   * @param UserManager    $user_manager
   * @param ProgramManager $program_manager
   * @param RemixManager   $remix_manager
   * @param EntityManager  $em
   */
  public function __construct(Filesystem $filesystem, UserManager $user_manager, ProgramManager $program_manager,
                                 RemixManager $remix_manager, EntityManager $em)
  {
    parent::__construct();
    $this->fileystem = $filesystem;
    $this->user_manager = $user_manager;
    $this->program_manager = $program_manager;
    $this->remix_manager = $remix_manager;
    $this->em = $em;
  }

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:legacy:import')
      ->setDescription('Import a legacy backup')
      ->addArgument('backupfile', InputArgument::REQUIRED, 'legacy backup file (tar.gz)');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;
    $this->filesystem = new Filesystem();
    $this->finder = new Finder();
    $this->screenshot_repository = $this->getContainer()->get('screenshotrepository');
    $this->catrobat_file_repository = $this->getContainer()->get('filerepository');

    CommandHelper::executeSymfonyCommand('catrobat:purge', $this->getApplication(), ['--force' => true], $output);

    $backup_file = $input->getArgument('backupfile');

    $this->importdir = $this->createTempDir();
    $this->writeln('Using Temp directory ' . $this->importdir);

    $temp_dir = $this->importdir;
    CommandHelper::executeShellCommand("tar xfz $backup_file --directory $temp_dir", ['timeout' => 3600], 'Extracting backupfile', $output);
    CommandHelper::executeShellCommand("tar xf $temp_dir/" . self::SQL_CONTAINER_FILE . " --directory $temp_dir", ['timeout' => 3600], 'Extracting SQL files', $output);
    CommandHelper::executeShellCommand("tar xfz $temp_dir/" . self::SQL_WEB_CONTAINER_FILE . " --directory $temp_dir", ['timeout' => 3600], 'Extracting Catroweb SQL files', $output);
    CommandHelper::executeShellCommand("tar xf $temp_dir/" . self::RESOURCE_CONTAINER_FILE . " --directory $temp_dir", ['timeout' => 3600], 'Extracting resource files', $output);

    $this->importUsers($this->importdir . '/' . self::TSV_USERS_FILE);
    $this->importPrograms($this->importdir . '/' . self::TSV_PROGRAMS_FILE);
    $this->importProgramFiles($this->importdir . '/' . self::TSV_PROGRAMS_FILE);

    $row = 0;
    $features_tsv = $this->importdir . '/' . self::TSV_FEATURED_PROGRAMS;
    if (($handle = fopen($features_tsv, 'r')) !== false)
    {
      while (($data = fgetcsv($handle, 0, "\t")) !== false)
      {
        $num = count($data);
        if ($num > 2)
        {
          $program = new FeaturedProgram();
          $program->setProgram($this->program_manager->find($data[1]));
          $program->setActive($data[3] === 't');
          $program->setNewFeaturedImage(new File($this->importdir . '/resources/featured/' . $data[1] . '.jpg'));
          $this->em->persist($program);
        }
        else
        {
          break;
        }
        ++$row;
      }
      $this->em->flush();
      fclose($handle);
      $this->writeln('Imported ' . $row . ' featured programs');
    }

    $this->filesystem->remove($temp_dir);
  }

  /**
   * @param $program_file
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  protected function importPrograms($program_file)
  {
    $row = 0;
    $skipped = 0;

    $progress = new ProgressBar($this->output);
    $progress->setFormat(' %current%/%max% [%bar%] %message%');
    $progress->start();

    $metadata = $this->em->getClassMetaData("App\Entity\Program");
    $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

    if (($handle = fopen($program_file, 'r')) !== false)
    {
      while (($data = fgetcsv($handle, 0, "\t")) !== false)
      {
        $num = count($data);
        if ($num > 2)
        {
          $id = $data[0];
          $language_version = $data[13];

          $progress->setMessage($data[1] . ' (' . $id . ')');
          $progress->advance();

          // ignore old programs except for manually changed ones - because FU
          if (version_compare($language_version, '0.8', '<') && $id != 821)
          {
            $progress->clear();
            $this->writeln('<error>Could not import program ' . $id . ' - version too old: ' . $language_version . '</error>');
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
          $program->setUploadedAt(new \DateTime($data[4], new \DateTimeZone('UTC')));
          $program->setUploadIp($data[5]);
          $program->setRemixMigratedAt(null);
          $program->setDownloads($data[6]);
          $program->setViews($data[7]);
          $program->setVisible($data[8] === 't');
          $program->setUser($this->user_manager->find($data[9]));
          $program->setUploadLanguage($data[10]);
          $program->setFilesize($data[11]);
          $program->setCatrobatVersionName($data[12]);

          if ($id == 821)
          {
            $program->setLanguageVersion('0.8');
          }
          {
            $program->setLanguageVersion($language_version);
          }

          $program->setApproved($data[20] === 't');
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
      $this->writeln('<info>Imported ' . $row . ' programs (Skipped ' . $skipped . ')</info>');
    }
  }

  /**
   * @param $program_file
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  protected function importProgramFiles($program_file)
  {
    $row = 0;
    $skipped = 0;

    $progress = new ProgressBar($this->output);
    $progress->setFormat(' %current%/%max% [%bar%] %message%');
    $progress->start();

    $metadata = $this->em->getClassMetaData("App\Entity\Program");
    $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

    if (($handle = fopen($program_file, 'r')) !== false)
    {
      while (($data = fgetcsv($handle, 0, "\t")) !== false)
      {
        $num = count($data);
        if ($num > 2)
        {
          $id = $data[0];
          $language_version = $data[13];

          $progress->setMessage($data[1] . ' (' . $id . ')');
          $progress->advance();

          if (version_compare($language_version, '0.8', '<') && $id != 821)
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
      $this->writeln('<info>Imported ' . $row . ' programs (Skipped ' . $skipped . ')</info>');
    }
  }

  /**
   * @param $user_file
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  protected function importUsers($user_file)
  {
    print_r($user_file);

    $row = 0;

    $progress = new ProgressBar($this->output);
    $progress->setFormat(' %current%/%max% [%bar%] %message%');
    $progress->start();

    $metadata = $this->em->getClassMetaData("App\Entity\User");
    $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

    if (($handle = fopen($user_file, 'r')) !== false)
    {
      while (($data = fgetcsv($handle, 0, "\t")) !== false)
      {
        $num = count($data);
        if ($num > 2)
        {
          // Special case - same email on two accounts, this one has no programs
          if ($data[1] == 'paul70078')
          {
            continue;
          }
          // Special case - no id 0
          if ($data[0] == 0)
          {
            continue;
          }

          $progress->setMessage($data[1] . ' (' . $data[0] . ')');
          $progress->advance();

          $user = new User();
          $user->setId($data[0]);
          $user->setUsername($data[1]);
          $user->setPassword($data[2]);
          $user->setEmail($data[3]);
          $user->setCountry(strtoupper($data[4]));
          $user->setUploadToken($data[11]);
          $user->setEnabled(true);
          $user->setAvatar(($data[13] === "\N") ? null : $data[13]);
          $user->setAdditionalEmail($data[14] === "\N" ? null : $data[14]);
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
      $this->writeln('<info>Imported ' . $row . ' users</info>');
    }
  }

  /**
   * @param $id
   */
  private function importScreenshots($id)
  {
    $screenhot_dir = $this->importdir . '/resources/thumbnails/';
    $screenshot_path = $screenhot_dir . $id . '_large.png';
    $thumbnail_path = $screenhot_dir . $id . '_small.png';
    if (file_exists($screenshot_path))
    {
      $this->screenshot_repository->importProgramAssets($screenshot_path, $thumbnail_path, $id);
    }
  }

  /**
   * @param $id
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  private function importProgramfile($id)
  {
    $filepath = $this->importdir . '/resources/projects/' . "$id" . '.catrobat';
    $async_http_client = new AsyncHttpClient(['timeout' => 12, 'max_number_of_concurrent_requests' => 10]);

    if (file_exists($filepath))
    {
      /**
       * @var $fileextractor CatrobatFileExtractor
       */
      $fileextractor = $this->getContainer()->get('fileextractor');
      $router = $this->getContainer()->get('router');
      $extracted_catrobat_file = $fileextractor->extract(new File($filepath));

      $program = $this->program_manager->find($id);
      $remix_updater = new RemixUpdater($this->remix_manager, $async_http_client, $router);
      $remix_updater->update($extracted_catrobat_file, $program);

      $this->catrobat_file_repository->saveProgram($extracted_catrobat_file, $id);
      $this->catrobat_file_repository->saveProgramfile(new File($filepath), $id);
    }
  }

  /**
   * @param $string
   */
  private function writeln($string)
  {
    if ($this->output != null)
    {
      $this->output->writeln($string);
    }
  }

  /**
   * @return bool|string
   */
  private function createTempDir()
  {
    $tempfile = tempnam(sys_get_temp_dir(), 'catimport');
    if (file_exists($tempfile))
    {
      unlink($tempfile);
    }
    mkdir($tempfile);
    if (is_dir($tempfile))
    {
      return $tempfile;
    }
  }
}
