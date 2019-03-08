<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforePersistEvent;
use App\Entity\Program;

/**
 * Class GameJamTagListener
 * @package App\Catrobat\Listeners
 */
class GameJamTagListener
{

  /**
   * @param ProgramBeforePersistEvent $event
   */
  public function onEvent(ProgramBeforePersistEvent $event)
  {
    $this->checkDescriptionTag($event->getProgramEntity());
  }

  /**
   * @param Program $program
   */
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
