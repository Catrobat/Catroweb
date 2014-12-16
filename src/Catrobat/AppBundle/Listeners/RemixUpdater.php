<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramRepository;
use Catrobat\AppBundle\Events\ProgramAfterInsertEvent;
use Catrobat\AppBundle\Model\ExtractedCatrobatFile;

class RemixUpdater
{
    const URL = "http://pocketcode.org/details/";
    private $repository;

    function __construct(ProgramRepository $repository)
    {
        $this->repository = $repository;
    }

    public function onProgramAfterInsert(ProgramAfterInsertEvent $event)
    {
        $this->update($event->getExtractedFile(), $event->getProgramEntity());
    }

    public function update(ExtractedCatrobatFile $file, Program $program)
    {
        $program_xml_properties = $file->getProgramXmlProperties();
        $program_xml_properties->header->remixOf = $program_xml_properties->header->url;
        if ($program_xml_properties->header->remixOf->__toString() != "") {
            preg_match("/([\d]+)$/",$program_xml_properties->header->url->__toString(),$matches);
            $parent = $this->repository->find(intval($matches[1]));
            if($parent != null) {
                $program->setRemixOf($parent);
            }
        }
        $program_xml_properties->header->url = self::URL . $program->getId();
        $program_xml_properties->header->userHandle = $program->getUser()->getUsername();
        $file->saveProgramXmlProperties();
    }
}
