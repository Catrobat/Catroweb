<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforePersistEvent;
use App\Entity\Program;

/**
 * Class GameJamTagListener.
 */
class GameJamTagListener
{
  public function onEvent(ProgramBeforePersistEvent $event)
  {
    $this->checkDescriptionTag($event->getProgramEntity());
  }

  public function checkDescriptionTag(Program $program)
  {
    if (null == $program->getGamejam() || null == $program->getGamejam()->getHashtag())
    {
      return;
    }

    $description = $program->getDescription();
    if (false === strpos($description, $program->getGamejam()->getHashtag()))
    {
      $description = $description."\n\n".$program->getGamejam()->getHashtag();
      $program->setDescription($description);
    }
  }
}
