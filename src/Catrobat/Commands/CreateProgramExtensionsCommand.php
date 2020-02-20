<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Services\ProgramFileRepository;
use App\Entity\Extension;
use App\Repository\ExtensionRepository;
use App\Repository\ProgramRepository;
use App\Catrobat\Exceptions\Upload\InvalidXmlException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use ZipArchive;


/**
 * Class CreateProgramExtensionsCommand
 * @package App\Catrobat\Commands
 */
class CreateProgramExtensionsCommand extends Command
{
  /**
   * @var OutputInterface
   */
  private $output;

  /**
   * @var EntityManagerInterface
   */
  private $em;

  /**
   * @var ProgramFileRepository
   */
  private $program_file_repository;

  /**
   * @var ProgramRepository
   */
  private $program_repository;

  /**
   * @var ExtensionRepository
   */
  private $extension_repository;


  /**
   * CreateProgramExtensionsCommand constructor.
   *
   * @param EntityManagerInterface $em
   * @param ProgramFileRepository $program_file_repository
   * @param ProgramRepository $program_repo
   * @param ExtensionRepository $extension_repository
   */
  public function __construct(EntityManagerInterface $em, ProgramFileRepository $program_file_repository,
                              ProgramRepository $program_repo, ExtensionRepository $extension_repository)
  {
    parent::__construct();
    $this->em = $em;
    $this->program_file_repository = $program_file_repository;
    $this->program_repository = $program_repo;
    $this->extension_repository = $extension_repository;
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
   * @param InputInterface $input
   * @param OutputInterface $output
   *
   * @return int|void|null
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

    $extensions = $this->extension_repository->findAll();

    foreach ($extensions as $extension)
    {
      $extension->removeAllPrograms();
      $this->em->persist($extension);
    }

    $this->em->flush();

    $finder = new Finder();
    $finder->in($this->program_file_repository->directory);

    $this->writeln("Searching for extensions ...");

    foreach ($finder as $element)
    {

      $zip = new ZipArchive();

      $open = $zip->open($this->program_file_repository->directory . $element->getFilename());

      if ($open !== true)
      {
        $this->writeln("Cant open: " . $this->program_file_repository->directory . $element->getFilename());
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
          $this->em->flush();
          $program_with_extensiones = false;
        }
      }
    }

    $this->writeln("Done!");
  }


  /**
   * @param File $element
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
