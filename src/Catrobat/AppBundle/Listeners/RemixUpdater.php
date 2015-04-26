<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramRepository;
use Catrobat\AppBundle\Events\ProgramAfterInsertEvent;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Symfony\Component\Routing\Router;

class RemixUpdater
{
    private $repository;
    private $router;

    function __construct(ProgramRepository $repository, Router $router)
    {
        $this->repository = $repository;
        $this->router = $router;
    }

    public function onProgramAfterInsert(ProgramAfterInsertEvent $event)
    {
        $this->update($event->getExtractedFile(), $event->getProgramEntity());
    }

    public function update(ExtractedCatrobatFile $file, Program $program)
    {
        $program_xml_properties = $file->getProgramXmlProperties();
        if ($program_xml_properties->header->url->__toString() != "") {
            preg_match("/([\d]+)$/",$program_xml_properties->header->url->__toString(),$matches);
            $program_id = intval($matches[1]);
            print_r($this->router->generate('program', array('id' => $program_id)));
            $program_xml_properties->header->remixOf = $program_xml_properties->header->url->__toString();
            $parent = $this->repository->find($program_id);
            if($parent != null) {
                $program->setRemixOf($parent);
            }
        }
        $program_xml_properties->header->url = $this->router->generate('program', array('id' => $program->getId()));
        $program_xml_properties->header->userHandle = $program->getUser()->getUsername();
        $file->saveProgramXmlProperties();
    }
}
