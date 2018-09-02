<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramBeforePersistEvent;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\Entity\Program;

class GameJamTagListener
{

  public function onEvent(ProgramBeforePersistEvent $event)
  {
    $this->checkDescriptionTag($event->getProgramEntity());
  }

  public function checkDescriptionTag(Program $program)
  {
    if ($program->getGamejam() == null || $program->getGamejam()->getHashtag() == null)
    {
      return;
    }

    $description = $program->getDescription();
    if (strpos($description, $program->getGamejam()->getHashtag()) === false)
    {
      $description = $description . "\n\n" . $program->getGamejam()->getHashtag();
      $program->setDescription($description);
    }
  }
}
