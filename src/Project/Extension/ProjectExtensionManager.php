<?php

namespace App\Project\Extension;

use App\DB\Entity\Project\Extension;
use App\DB\Entity\Project\Project;
use App\DB\EntityRepository\Project\ExtensionRepository;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ProjectExtensionManager
{
  public function __construct(
    protected ExtensionRepository $extension_repository,
    protected LoggerInterface $logger,
    protected EntityManagerInterface $entity_manager
  ) {
  }

  public function addExtensions(ExtractedCatrobatFile $extracted_file, Project $project, bool $flush = true): void
  {
    $project->removeAllExtensions();

    $code_xml = strval($extracted_file->getProjectXmlProperties()->asXML());

    // What about drone, raspberry, chromecast ?
    $this->addArduinoExtensions($project, $code_xml);
    $this->addPhiroExtensions($project, $code_xml);
    $this->addEmbroideryExtensions($project, $code_xml);
    $this->addMindstormsExtensions($project, $code_xml);
    $this->addMultiplayerExtensions($project, $code_xml);

    $this->saveProject($project, $flush);
  }

  public function addMultiplayerExtensions(Project $project, string $code_xml): void
  {
    if ($this->isMultiplayerProject($code_xml)) {
      $extension = $this->getExtension(Extension::MULTIPLAYER);
      if (!is_null($extension)) {
        $project->addExtension($extension);
      }
    }
  }

  public function addArduinoExtensions(Project $project, string $code_xml): void
  {
    if ($this->isAnArduinoProject($code_xml)) {
      $extension = $this->getExtension(Extension::ARDUINO);
      if (!is_null($extension)) {
        $project->addExtension($extension);
      }
    }
  }

  public function addEmbroideryExtensions(Project $project, string $code_xml): void
  {
    if ($this->isAnEmbroideryProject($code_xml)) {
      $extension = $this->getExtension(Extension::EMBROIDERY);
      if (!is_null($extension)) {
        $project->addExtension($extension);
      }
    }
  }

  public function addMindstormsExtensions(Project $project, string $code_xml): void
  {
    if ($this->isAMindstormsProject($code_xml)) {
      $extension = $this->getExtension(Extension::MINDSTORMS);
      if (!is_null($extension)) {
        $project->addExtension($extension);
      }
    }
  }

  public function addPhiroExtensions(Project $project, string $code_xml): void
  {
    if ($this->isAPhiroProject($code_xml)) {
      $extension = $this->getExtension(Extension::PHIRO);
      if (!is_null($extension)) {
        $project->addExtension($extension);
      }
    }
  }

  protected function isAnArduinoProject(string $code_xml): bool
  {
    return str_contains($code_xml, '<brick type="Arduino');
  }

  protected function isMultiplayerProject(string $code_xml): bool
  {
    return str_contains($code_xml, '<programMultiplayerVariableList>') && str_contains($code_xml, '</programMultiplayerVariableList>');
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
   * @throws \Exception
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

  protected function saveProject(Project $project, bool $flush = true): void
  {
    $this->entity_manager->persist($project);
    if ($flush) {
      $this->entity_manager->flush();
    }
  }
}
