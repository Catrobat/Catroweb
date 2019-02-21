<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Entity\Extension;
use Catrobat\AppBundle\Entity\ProgramRepository;
use Catrobat\AppBundle\Exceptions\Upload\InvalidXmlException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;


/**
 * Class CreateProgramExtensionsCommand
 * @package Catrobat\AppBundle\Commands
 */
class CreateProgramExtensionsCommand extends ContainerAwareCommand
{
  /**
   * @var OutputInterface
   */
  private $output;

  /**
   * @var EntityManager
   */
  private $em;

  /**
   * @var
   */
  private $programfile_directory;

  /**
   * @var ProgramRepository
   */
  private $program_repository;


  /**
   * CreateProgramExtensionsCommand constructor.
   *
   * @param EntityManager     $em
   * @param string            $programfile_directory
   * @param ProgramRepository $program_repo
   */
  public function __construct(EntityManager $em, $programfile_directory, $program_repo)
  {
    parent::__construct();
    $this->em = $em;
    $this->programfile_directory = $programfile_directory;
    $this->program_repository = $program_repo;
  }


  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:create:extensions')
      ->setDescription('Creating extensions from uploaded programs');
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
    /**
     * @var $extension Extension
     */
    $this->output = $output;
    $xpath = '//@category';
    $program_with_extensiones = false;

    $this->writeln("Deleting all linked extensions");

    $extension_repository = $this->getContainer()->get('extensionrepository');
    $extensions = $extension_repository->findAll();

    foreach ($extensions as $extension)
    {
      $extension->removeAllPrograms();
      $this->em->persist($extension);
    }

    $this->em->flush();

    $finder = new Finder();
    $finder->in($this->programfile_directory);

    $this->writeln("Searching for extensions ...");

    foreach ($finder as $element)
    {

      $zip = new \ZipArchive();

      $open = $zip->open($this->programfile_directory . $element->getFilename());

      if ($open !== true)
      {
        $this->writeln("Cant open: " . $this->programfile_directory . $element->getFilename());
        $this->writeln("Skipping file ...");
        continue;
      }

      $program = $this->getProgram($element);

      if ($program == null)
      {
        $this->writeln("Cant find database entry for file: " . $element->getFilename());
        $this->writeln("Skipping file ...");
        continue;
      }

      $content = $zip->getFromName("code.xml");

      if ($content === false)
      {
        throw new InvalidXmlException();
      }
      $content = str_replace('&#x0;', '', $content, $count);

      $xml = simplexml_load_string($content);

      if ($xml === false)
      {
        $this->writeln("Cant load code.xml from: " . $element->getFilename());
      }
      else
      {
        $nodes = $xml->xpath($xpath);
      }

      if (!empty($nodes))
      {

        $prefixes = array_map(function ($elem) {
          return explode("_", $elem['category'], 2)[0];
        }, $nodes);
        $prefixes = array_unique($prefixes);

        foreach ($extensions as $extension)
        {
          if (in_array($extension->getPrefix(), $prefixes))
          {
            $program->addExtension($extension);
            $program_with_extensiones = true;

            if ($extension->getPrefix() == 'PHIRO')
            {
              $program->setFlavor('phirocode');
            }
          }

          if (strcmp($extension->getPrefix(), 'CHROMECAST') == 0)
          {
            $is_cast = $xml->xpath('header/isCastProject');

            if (!empty($is_cast))
            {
              $cast_value = ((array)$is_cast[0]);
              if (strcmp($cast_value[0], 'true') == 0)
              {
                $program->addExtension($extension);
                $program_with_extensiones = true;
              }
            }
          }
        }

        if ($program_with_extensiones == true)
        {
          $this->em->persist($program);
          $this->em->flush($program);
          $program_with_extensiones = false;
        }
      }
    }

    $this->writeln("Done!");
  }


  /**
   * @param $element
   *
   * @return object|null
   */
  private function getProgram($element)
  {
    $id = explode(".", $element->getFilename());

    return $this->program_repository->find($id[0]);
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
}
