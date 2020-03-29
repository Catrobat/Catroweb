<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforePersistEvent;
use App\Entity\Program;

class GameJamTagListener
{
  public function onEvent(ProgramBeforePersistEvent $event): void
  {
    $this->checkDescriptionTag($event->getProgramEntity());
  }

  public function checkDescriptionTag(Program $program): void
  {
    if (null == $program->getGamejam() || null == $program->getGamejam()->getHashtag())
    {
      return;
    }

    $description = $program->getDescription();
    if (false === strpos($description, (string) $program->getGamejam()->getHashtag()))
    {
      $description = $description."\n\n".$program->getGamejam()->getHashtag();
      $program->setDescription($description);
    }
  }
}
