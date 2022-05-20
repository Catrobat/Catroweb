<?php

namespace App\Project\CatrobatFile;

use App\DB\Entity\Project\Extension;
use App\DB\Entity\Project\Program;
use App\DB\EntityRepository\Project\ExtensionRepository;
use App\Project\Event\ProgramBeforePersistEvent;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class ProgramExtensionListener
{
  public function __construct(protected ExtensionRepository $extension_repository, protected LoggerInterface $logger, protected EntityManagerInterface $entity_manager)
  {
  }

  public function onEvent(ProgramBeforePersistEvent $event): void
  {
    $this->addExtensions($event->getExtractedFile(), $event->getProgramEntity());
  }

  public function addExtensions(ExtractedCatrobatFile $extracted_file, Program $program, bool $flush = true): void
  {
    $program->removeAllExtensions();

    $code_xml = strval($extracted_file->getProgramXmlProperties()->asXML());

    // What about drone, raspberry, chromecast ?
    $this->addArduinoExtensions($program, $code_xml);
    $this->addPhiroExtensions($program, $code_xml);
    $this->addEmbroideryExtensions($program, $code_xml);
    $this->addMindstormsExtensions($program, $code_xml);

    $this->saveProject($program, $flush);
  }

  public function addArduinoExtensions(Program $program, string $code_xml): void
  {
    if ($this->isAnArduinoProject($code_xml)) {
      $extension = $this->getExtension(Extension::ARDUINO);
      if (!is_null($extension)) {
        $program->addExtension($extension);
      }
    }
  }

  public function addEmbroideryExtensions(Program $program, string $code_xml): void
  {
    if ($this->isAnEmbroideryProject($code_xml)) {
      $extension = $this->getExtension(Extension::EMBROIDERY);
      if (!is_null($extension)) {
        $program->addExtension($extension);
      }
    }
  }

  public function addMindstormsExtensions(Program $program, string $code_xml): void
  {
    if ($this->isAMindstormsProject($code_xml)) {
      $extension = $this->getExtension(Extension::MINDSTORMS);
      if (!is_null($extension)) {
        $program->addExtension($extension);
      }
    }
  }

  public function addPhiroExtensions(Program $program, string $code_xml): void
  {
    if ($this->isAPhiroProject($code_xml)) {
      $extension = $this->getExtension(Extension::PHIRO);
      if (!is_null($extension)) {
        $program->addExtension($extension);
      }
    }
  }

  protected function isAnArduinoProject(string $code_xml): bool
  {
    return str_contains($code_xml, '<brick type="Arduino');
  }

  protected function isAnEmbroideryProject(string $code_xml): bool
  {
    return str_contains($code_xml, '<brick type="StitchBrick">');
  }

  protected function isAMindstormsProject(string $code_xml): bool
  {
    return 1 === preg_match('/\"legonxt|\"legoev3/i', $code_xml, $matches);
  }

  protected function isAPhiroProject(string $code_xml): bool
  {
    return str_contains($code_xml, '<brick type="Phiro');
  }

  /**
   * @throws Exception
   */
  protected function getExtension(string $internal_title): ?Extension
  {
    /** @var Extension|null $extension */
    $extension = $this->extension_repository->findOneBy(['internal_title' => $internal_title]);
    if (null === $extension) {
      $this->logger->alert("Extension `{$internal_title}` is missing!");
    }

    return $extension;
  }

  protected function saveProject(Program $project, bool $flush = true): void
  {
    $this->entity_manager->persist($project);
    if ($flush) {
      $this->entity_manager->flush();
    }
  }
}
