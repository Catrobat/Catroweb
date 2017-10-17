<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Entity\ExtensionRepository;
use Catrobat\AppBundle\Events\ProgramBeforePersistEvent;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\Entity\Program;

class ProgramExtensionListener
{
    private $extension_repository;

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
        $xml = $extracted_file->getProgramXmlProperties();

        $xpath = '//@category';
        $nodes = $xml->xpath($xpath);

        $program->removeAllExtensions();

        if (empty($nodes))
            return;

        $prefixes = array_map(function ($element) { return explode("_", $element['category'], 2)[0]; }, $nodes);
        $prefixes = array_unique($prefixes);

        $extensions = $this->extension_repository->findAll();
        
        foreach ($extensions as $extension) {
            if (in_array($extension->getPrefix(), $prefixes )) {
                $program->addExtension($extension);

              if ($extension->getPrefix() == 'PHIRO') {
                $program->setFlavor('phirocode');
              }
            }
        }
    }
}
