<?php
namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Catrobat\AppBundle\Model\ProgramManager;
use Catrobat\AppBundle\Model\UserManager;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Process\Process;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\Validator\Constraints\DateTime;
use Catrobat\AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Catrobat\AppBundle\Entity\FeaturedProgram;

class ImportLegacyCommand extends ContainerAwareCommand
{
    const RESOURCE_CONTAINER_FILE = "resources.tar";
    const SQL_CONTAINER_FILE = "sql.tar";
    const SQL_WEB_CONTAINER_FILE = "catroweb-sql.tar.gz";
    const TSV_USERS_FILE = "2034.dat";
    const TSV_PROGRAMS_FILE = "2041.dat";
    const TSV_FEATURED_PROGRAMS = "2037.dat";

    private $fileystem;
    private $user_manager;
    private $program_manager;
    private $output;

    public function __construct(Filesystem $filesystem, UserManager $user_manager, ProgramManager $program_manager, EntityManager $em)
    {
        parent::__construct();
        $this->fileystem = $filesystem;
        $this->user_manager = $user_manager;
        $this->program_manager = $program_manager;
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setName('catrobat:legacy:import')
            ->setDescription('Import a legacy backup')
            ->addArgument('backupfile', InputArgument::REQUIRED, 'legacy backup file (tar.gz)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $filesystem = new Filesystem();
        $finder = new Finder();

        $storage_dir = $this->getContainer()->getParameter('catrobat.file.storage.dir');

        $this->deleteUserFiles();
        $this->deleteDatabase();

        $backup_file = $input->getArgument('backupfile');
        $temp_dir = $this->createTempDir();

        $this->executeShellCommand("tar xfz $backup_file --directory $temp_dir", "Extracting backupfile");
        $this->executeShellCommand("tar xf $temp_dir/".self::SQL_CONTAINER_FILE." --directory $temp_dir", "Extracting SQL files");
        $this->executeShellCommand("tar xfz $temp_dir/".self::SQL_WEB_CONTAINER_FILE." --directory $temp_dir", "Extracting Catroweb SQL files");
        $this->executeShellCommand("tar xf $temp_dir/".self::RESOURCE_CONTAINER_FILE." --directory $temp_dir", "Extracting resource files");
        
        $finder->in($temp_dir."/resources/projects")->depth(0);
        foreach ($finder as $file) {
            $filesystem->rename($file->getRealpath(), $storage_dir."/".$file->getFilename());
        }

        $this->importUsers($temp_dir."/".self::TSV_USERS_FILE);
        $this->importProgramMetadata($temp_dir."/".self::TSV_PROGRAMS_FILE);

        /**
         * Create Thumbnails *
         */
        $screenshot_repository = $this->getContainer()->get('screenshotrepository');
        $finder->in($temp_dir."/resources/thumbnails/")
            ->name('*_large.png')
            ->notName("thumbnail_large.png")
            ->depth(0);

        foreach ($finder as $file) {
            $parts = explode("_", $file->getFilename());
            $id = intval($parts[0]);
            $screenshot_repository->saveProgramAssets($file->getRealpath(), $id);
            $this->write(".");
        }

        $row = 0;
        $features_tsv = $temp_dir."/".self::TSV_FEATURED_PROGRAMS;
        if (($handle = fopen($features_tsv, "r")) !== false) {
            while (($data = fgetcsv($handle, 0, "\t")) !== false) {
                $num = count($data);
                if ($num > 2) {
                    $program = new FeaturedProgram();
                    $program->setProgram($this->program_manager->find($data[1]));
                    $program->setActive($data[3] === "t");
                    $program->setNewFeaturedImage(new File($temp_dir."/resources/featured/".$data[1].".jpg"));
                    $this->em->persist($program);
                } else {
                    break;
                }
                $row ++;
            }
            $this->em->flush();
            fclose($handle);
            $this->writeln("Imported ".$row." featured programs");
        }

        $filesystem->remove($temp_dir);
    }

    protected function deleteDatabase()
    {
        $this->executeShellCommand("php app/console doctrine:schema:drop --force", "droping schema");
        $this->executeShellCommand("php app/console doctrine:schema:create", "creating schema");
    }

    protected function importProgramMetadata($program_file)
    {
        $row = 0;

        $metadata = $this->em->getClassMetaData("Catrobat\AppBundle\Entity\Program");
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        if (($handle = fopen($program_file, "r")) !== false) {
            while (($data = fgetcsv($handle, 0, "\t")) !== false) {
                $num = count($data);
                if ($num > 2) {
                    $this->writeln("Inserting ".$data[1]);
                    $program = new Program();
                    $program->setId($data[0]);
                    $program->setName($data[1]);
                    $program->setDescription($data[2]);
                    $program->setUploadedAt(new \DateTime($data[4], new \DateTimeZone('UTC')));
                    $program->setUploadIp($data[5]);
                    $program->setDownloads($data[6]);
                    $program->setViews($data[7]);
                    $program->setVisible($data[8] === "t");
                    $program->setUser($this->user_manager->find($data[9]));
                    $program->setUploadLanguage($data[10]);
                    $program->setFilesize($data[11]);
                    $program->setCatrobatVersionName($data[12]);
                    $program->setRemixCount($data[19]);
                    $program->setApproved($data[20] === "t");

                    $program->setLanguageVersion(1);
                    $program->setFilename("0.catrobat");
                    $program->setThumbnail("thumb.png");
                    $program->setScreenshot("screenshot.png");
                    $program->setCatrobatVersion(1);
                    $this->em->persist($program);
                } else {
                    break;
                }
                $row ++;
            }
            $this->em->flush();
            fclose($handle);
            $this->writeln("Imported ".$row." programs");
        }
    }

    protected function importUsers($user_file)
    {
        print_r($user_file);

        $row = 0;

        $metadata = $this->em->getClassMetaData("Catrobat\AppBundle\Entity\User");
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        if (($handle = fopen($user_file, "r")) !== false) {
            while (($data = fgetcsv($handle, 0, "\t")) !== false) {
                $num = count($data);
                if ($num > 2) {
                    // Special case - same email on two accounts, this one has no programs
                    if ($data[1] == "paul70078") {
                        continue;
                    }
                    // Special case - no id 0
                    if ($data[0] == 0) {
                        continue;
                    }

                    $this->writeln("Inserting ".$data[0]." - ".$data[1]);
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
                } else {
                    break;
                }
                $row ++;
            }
            $this->em->flush();
            fclose($handle);
            $this->writeln("Imported ".$row." users");
        }
    }

    private function deleteUserFiles()
    {
        $this->emptyDirectory($this->getContainer()
            ->getParameter('catrobat.screenshot.dir'), "Delete screenshots");
        $this->emptyDirectory($this->getContainer()
            ->getParameter('catrobat.thumbnail.dir'), "Delete thumnails");
        $this->emptyDirectory($this->getContainer()
            ->getParameter('catrobat.file.storage.dir'), "Delete programs");
        $this->emptyDirectory($this->getContainer()
            ->getParameter('catrobat.file.extract.dir'), "Delete extracted programs");
        $this->emptyDirectory($this->getContainer()
            ->getParameter('catrobat.featuredimage.dir'), "Delete feature imagess");
    }

    private function executeShellCommand($command, $description)
    {
        $this->write($description." ('".$command."') ... ");
        $process = new Process($command);
        $process->setTimeout(3600);
        $process->run();
        if ($process->isSuccessful()) {
            $this->writeln("OK");

            return true;
        } else {
            $this->writeln("failed!");

            return false;
        }
    }

    private function emptyDirectory($directory, $description)
    {
        $this->write($description." ('".$directory."') ... ");
        if ($directory == "") {
            $this->writeln("failed");

            return;
        }

        $filesystem = new Filesystem();

        $finder = new Finder();
        $finder->in($directory)->depth(0);
        foreach ($finder as $file) {
            $filesystem->remove($file);
        }
        $this->writeln("OK");
    }

    private function write($string)
    {
        if ($this->output != null) {
            $this->output->write($string);
        }
    }

    private function writeln($string)
    {
        if ($this->output != null) {
            $this->output->writeln($string);
        }
    }

    private function createTempDir()
    {
        $tempfile = tempnam(sys_get_temp_dir(), 'catimport');
        if (file_exists($tempfile)) {
            unlink($tempfile);
        }
        mkdir($tempfile);
        if (is_dir($tempfile)) {
            return $tempfile;
        }
    }
}
