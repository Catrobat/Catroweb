<?php

declare(strict_types=1);

namespace App\System\Commands;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;

/**
 * symfony/console 7.4 (behat/behat does not allow 8 yet) adds its default commands through the deprecated add()
 * whenever add() and addCommand() are declared in different classes, and FrameworkBundle 8 overrides only
 * addCommand(): every console run logged the deprecation. Declaring add() here routes it to addCommand().
 *
 * ponytail: delete this class (and use FrameworkBundle's Application again) once symfony/console is on 8.x.
 */
class ConsoleApplication extends Application
{
  #[\Override]
  public function add(Command $command): ?Command
  {
    return $this->addCommand($command);
  }
}
