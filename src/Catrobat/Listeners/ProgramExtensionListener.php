<?php

namespace App\Catrobat\Listeners;

use App\Entity\Extension;
use App\Repository\ExtensionRepository;
use App\Catrobat\Events\ProgramBeforePersistEvent;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Entity\Program;


/**
 * Class ProgramExtensionListener
 * @package App\Catrobat\Listeners
 */
class ProgramExtensionListener
{
  /**
   * @var ExtensionRepository
   */
  private $extension_repository;

  /**
   * ProgramExtensionListener constructor.
   *
   * @param ExtensionRepository $repo
   */
  public function __construct(ExtensionRepository $repo)
  {
    $this->extension_repository = $repo;
  }

  /**
   * @param ProgramBeforePersistEvent $event
   */
  public function onEvent(ProgramBeforePersistEvent $event)
  {
    $this->checkExtension($event->getExtractedFile(), $event->getProgramEntity());
  }

  /**
   * @param ExtractedCatrobatFile $extracted_file
   * @param Program               $program
   */
  public function checkExtension(ExtractedCatrobatFile $extracted_file, Program $program)
  {
    /**
     * @var $extension Extension
     */

    $xml = $extracted_file->getProgramXmlProperties();

    $xpath = '//@category';
    $nodes = $xml->xpath($xpath);

    $program->removeAllExtensions();

    if (empty($nodes))
    {
      return;
    }

    $prefixes = array_map(function ($element) {
      return explode("_", $element['category'], 2)[0];
    }, $nodes);
    $prefixes = array_unique($prefixes);

    $extensions = $this->extension_repository->findAll();

    foreach ($extensions as $extension)
    {
      if (in_array($extension->getPrefix(), $prefixes))
      {
        $program->addExtension($extension);

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
          }
        }
      }
    }
  }
}
