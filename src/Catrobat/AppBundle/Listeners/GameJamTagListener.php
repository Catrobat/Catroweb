<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramBeforePersistEvent;
use Catrobat\AppBundle\Entity\Program;

/**
 * Class GameJamTagListener
 * @package Catrobat\AppBundle\Listeners
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
