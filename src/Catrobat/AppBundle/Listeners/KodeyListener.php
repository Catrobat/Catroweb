<?php
namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramBeforePersistEvent;
use Catrobat\AppBundle\Model\ExtractedCatrobatFile;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\Program;
use Buzz\Exception\RuntimeException;
class KodeyListener
{
    const KODEY_PERMISSION = "BLUETOOTH_KODEY";
    
    public function onEvent(ProgramBeforePersistEvent $event)
    {
        $this->checkKodey($event->getExtractedFile(), $event->getProgramEntity());
    }
    
    public function checkKodey(ExtractedCatrobatFile $extracted_file, Program $program)
    {
        $handle = @fopen($extracted_file->getPath() . "/permissions.txt", "r");
        $is_kodey = false;
        if ($handle) {
            while (($line = fgets($handle)) !== false)
            {
                if (strcmp($line, self::KODEY_PERMISSION . "\n") === 0)
                {
                    $is_kodey = true;
                    break;
                }
            }
            fclose($handle);
        }
        $program->setKodey($is_kodey);
    }
}