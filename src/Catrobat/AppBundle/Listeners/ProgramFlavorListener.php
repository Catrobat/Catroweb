<?php
namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramBeforePersistEvent;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Model\ExtractedCatrobatFile;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\StatusCode;

class ProgramFlavorListener
{
    const KODEY_PERMISSION = "BLUETOOTH_KODEY";
    
    public function onEvent(ProgramBeforePersistEvent $event)
    {
        $this->checkFlavor($event->getExtractedFile(), $event->getProgramEntity());
    }
    
    public function checkFlavor(ExtractedCatrobatFile $file, Program $program)
    {

        $program_xml_properties = $file->getProgramXmlProperties();
        $appName = $program_xml_properties->header->applicationName->__toString();

        if($appName === 'Pocket Code')
        {
            $program->setFlavor('pocketcode');
        }
        else if($appName === 'Pocket Kodey')
        {
            $program->setFlavor('pocketkodey');
        }
        else
        {
            throw new InvalidCatrobatFileException("Unknown application!",StatusCode::INTERNAL_SERVER_ERROR);
        }
    }
}