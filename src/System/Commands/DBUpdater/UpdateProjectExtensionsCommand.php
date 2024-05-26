<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater;

use App\DB\Entity\Project\Extension;
use App\DB\EntityRepository\Project\ExtensionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:update:extensions', description: 'Inserting our static project extension into the Database')]
class UpdateProjectExtensionsCommand extends Command
{
  final public const string EXTENSION_LTM_PREFIX = 'extensions.extension.';

  public function __construct(private readonly EntityManagerInterface $entity_manager, private readonly ExtensionRepository $extension_repository)
  {
    parent::__construct();
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $count = 0;

    $extension = $this->getOrCreateExtension(Extension::ARDUINO)
      ->setTitleLtmCode(self::EXTENSION_LTM_PREFIX.'arduino.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($extension);

    $extension = $this->getOrCreateExtension(Extension::DRONE)
      ->setTitleLtmCode(self::EXTENSION_LTM_PREFIX.'drone.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($extension);

    $extension = $this->getOrCreateExtension(Extension::PHIRO)
      ->setTitleLtmCode(self::EXTENSION_LTM_PREFIX.'phiro.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($extension);

    $extension = $this->getOrCreateExtension(Extension::RASPBERRY_PI)
      ->setTitleLtmCode(self::EXTENSION_LTM_PREFIX.'raspberry_pi.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($extension);

    $extension = $this->getOrCreateExtension(Extension::EMBROIDERY)
      ->setTitleLtmCode(self::EXTENSION_LTM_PREFIX.'embroidery.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($extension);

    $extension = $this->getOrCreateExtension(Extension::MINDSTORMS)
      ->setTitleLtmCode(self::EXTENSION_LTM_PREFIX.'mindstorms.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($extension);

    $extension = $this->getOrCreateExtension(Extension::MULTIPLAYER)
      ->setTitleLtmCode(self::EXTENSION_LTM_PREFIX.'multiplayer.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($extension);

    $this->entity_manager->flush();

    $output->writeln($count.' Extensions in the Database have been inserted/updated');

    return 0;
  }

  protected function getOrCreateExtension(string $internal_title): Extension
  {
    $extension = $this->extension_repository->findOneBy(['internal_title' => $internal_title]) ?? new Extension();

    return $extension->setInternalTitle($internal_title);
  }
}
