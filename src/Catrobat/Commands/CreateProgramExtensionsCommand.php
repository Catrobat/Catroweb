<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Exceptions\Upload\InvalidXmlException;
use App\Catrobat\Services\ProgramFileRepository;
use App\Entity\Extension;
use App\Repository\ExtensionRepository;
use App\Repository\ProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use ZipArchive;

/**
 * Class CreateProgramExtensionsCommand.
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

  protected function configure()
  {
    $this->setName('catrobat:create:extensions')
      ->setDescription('Creating extensions from uploaded programs')
    ;
  }

  /**
   * @return int|void|null
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    /*
     * @var $extension Extension
     */
    $this->output = $output;
    $xpath = '//@category';
    $program_with_extensiones = false;

    $this->writeln('Deleting all linked extensions');

    $extensions = $this->extension_repository->findAll();

    foreach ($extensions as $extension)
    {
      $extension->removeAllPrograms();
      $this->em->persist($extension);
    }

    $this->em->flush();

    $finder = new Finder();
    $finder->in($this->program_file_repository->directory);

    $this->writeln('Searching for extensions ...');

    foreach ($finder as $element)
    {
      $zip = new ZipArchive();

      $open = $zip->open($this->program_file_repository->directory.$element->getFilename());

      if (true !== $open)
      {
        $this->writeln('Cant open: '.$this->program_file_repository->directory.$element->getFilename());
        $this->writeln('Skipping file ...');
        continue;
      }

      $program = $this->getProgram($element);

      if (null == $program)
      {
        $this->writeln('Cant find database entry for file: '.$element->getFilename());
        $this->writeln('Skipping file ...');
        continue;
      }

      $content = $zip->getFromName('code.xml');

      if (false === $content)
      {
        throw new InvalidXmlException();
      }
      $content = str_replace('&#x0;', '', $content, $count);

      $xml = simplexml_load_string($content);

      if (false === $xml)
      {
        $this->writeln('Cant load code.xml from: '.$element->getFilename());
      }
      else
      {
        $nodes = $xml->xpath($xpath);
      }

      if (!empty($nodes))
      {
        $prefixes = array_map(function ($elem)
        {
          return explode('_', $elem['category'], 2)[0];
        }, $nodes);
        $prefixes = array_unique($prefixes);

        foreach ($extensions as $extension)
        {
          if (in_array($extension->getPrefix(), $prefixes, true))
          {
            $program->addExtension($extension);
            $program_with_extensiones = true;

            if ('PHIRO' == $extension->getPrefix())
            {
              $program->setFlavor('phirocode');
            }
          }

          if (0 == strcmp($extension->getPrefix(), 'CHROMECAST'))
          {
            $is_cast = $xml->xpath('header/isCastProject');

            if (!empty($is_cast))
            {
              $cast_value = ((array) $is_cast[0]);
              if (0 == strcmp($cast_value[0], 'true'))
              {
                $program->addExtension($extension);
                $program_with_extensiones = true;
              }
            }
          }
        }

        if (true == $program_with_extensiones)
        {
          $this->em->persist($program);
          $this->em->flush();
          $program_with_extensiones = false;
        }
      }
    }

    $this->writeln('Done!');
  }

  /**
   * @param File $element
   *
   * @return object|null
   */
  private function getProgram($element)
  {
    $id = explode('.', $element->getFilename());

    return $this->program_repository->find($id[0]);
  }

  /**
   * @param $string
   */
  private function writeln($string)
  {
    if (null != $this->output)
    {
      $this->output->writeln($string);
    }
  }
}
