<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforePersistEvent;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Entity\Extension;
use App\Entity\Program;
use App\Repository\ExtensionRepository;

/**
 * Class ProgramExtensionListener.
 */
class ProgramExtensionListener
{
  /**
   * @var ExtensionRepository
   */
  private $extension_repository;

  /**
   * ProgramExtensionListener constructor.
   */
  public function __construct(ExtensionRepository $repo)
  {
    $this->extension_repository = $repo;
  }

  public function onEvent(ProgramBeforePersistEvent $event)
  {
    $this->checkExtension($event->getExtractedFile(), $event->getProgramEntity());
  }

  public function checkExtension(ExtractedCatrobatFile $extracted_file, Program $program)
  {
    /**
     * @var Extension
     */
    $xml = $extracted_file->getProgramXmlProperties();

    $xpath = '//@category';
    $nodes = $xml->xpath($xpath);

    $program->removeAllExtensions();

    if (empty($nodes))
    {
      return;
    }

    $prefixes = array_map(function ($element)
    {
      return explode('_', $element['category'], 2)[0];
    }, $nodes);
    $prefixes = array_unique($prefixes);

    $extensions = $this->extension_repository->findAll();

    foreach ($extensions as $extension)
    {
      if (in_array($extension->getPrefix(), $prefixes, true))
      {
        $program->addExtension($extension);

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
          }
        }
      }
    }
  }
}
