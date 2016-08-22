<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Services\CatrobatFileExtractor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Entity\Tag;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Process\Process;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Catrobat\AppBundle\Entity\FeaturedProgram;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Translation\TranslatorInterface;
use Catrobat\AppBundle\Entity\TagRepository;

class CreateProgramExtensionsCommand extends ContainerAwareCommand
{

    private $output;
    private $em;
    private $programfile_directory;
    private $program_repository;

    public function __construct(EntityManager $em, $programfile_directory, $program_repo)
    {
        parent::__construct();
        $this->em = $em;
        $this->programfile_directory = $programfile_directory;
        $this->program_repository = $program_repo;
    }

    protected function configure()
    {
        $this->setName('catrobat:create:extensions')
            ->setDescription('Creating extensions from uploaded programs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $xpath = '//@category';
        $program_with_extensiones = false;

        $this->writeln("Deleting all linked extensions");

        $extension_repository = $this->getContainer()->get('extensionrepository');
        $extensions = $extension_repository->findAll();

        foreach ($extensions as $extension) {
            $extension->removeAllPrograms();
            $this->em->persist($extension);
        }

        $this->em->flush();

        $finder = new Finder();
        $finder->in($this->programfile_directory);

        $this->writeln("Searching for extensions ...");

        foreach ($finder as $element) {

            $zip = new \ZipArchive();
            $open = $zip->open($this->programfile_directory . $element->getFilename());

            if( $open !== true ){
                $this->writeln("Cant open: " . $this->programfile_directory . $element->getFilename());
                $this->writeln("Skipping file ...");
                continue;
            }

            $program = $this->getProgram($element);

            if($program == null) {
                $this->writeln("Cant find database entry for file: " . $element->getFilename());
                $this->writeln("Skipping file ...");
                continue;
            }

            $xml = @simplexml_load_string($zip->getFromName("code.xml"));

            $nodes = $xml->xpath($xpath);

            if (!empty($nodes)) {

                $prefixes = array_map(function ($elem) { return explode("_", $elem['category'], 2)[0]; }, $nodes);
                $prefixes = array_unique($prefixes);

                foreach ($extensions as $extension) {
                    if (in_array($extension->getPrefix(), $prefixes )) {
                        $program->addExtension($extension);
                        $program_with_extensiones = true;
                    }
                }

                if ($program_with_extensiones == true) {
                    $this->em->persist($program);
                    $this->em->flush($program);
                    $program_with_extensiones = false;
                }
            }
        }

        $this->writeln("Done!");
    }

    private function getProgram($element)
    {
        $id = explode(".",$element->getFilename());
        return $this->program_repository->find($id[0]);
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
}
