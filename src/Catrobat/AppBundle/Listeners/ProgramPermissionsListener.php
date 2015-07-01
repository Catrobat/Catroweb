<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramBeforePersistEvent;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\Entity\Program;

class ProgramPermissionsListener
{
    const PHIRO_PERMISSION = 'BLUETOOTH_PHIRO';
    const LEGO_PERMISSION = 'BLUETOOTH_LEGO_NXT';

    public function onEvent(ProgramBeforePersistEvent $event)
    {
        $this->checkPermissions($event->getExtractedFile(), $event->getProgramEntity());
    }

    public function checkPermissions(ExtractedCatrobatFile $extracted_file, Program $program)
    {
        $handle = @fopen($extracted_file->getPath().'/permissions.txt', 'r');
        $program->setPhiro(false);
        $program->setLego(false);
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (strcmp($line, self::PHIRO_PERMISSION."\n") === 0) {
                    $program->setPhiro(true);
                } elseif (strcmp($line, self::LEGO_PERMISSION."\n") === 0) {
                    $program->setLego(true);
                }
            }
            fclose($handle);
        }
    }
}
