<?php

namespace App\System\Commands\Create;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Special\FeaturedProgram;
use App\DB\EntityRepository\FlavorRepository;
use App\Project\ProgramManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;

class CreateFeatureProgramCommand extends Command
{
  protected static $defaultDescription = 'feature a project';

  public function __construct(
    private readonly ProgramManager $program_manager,
    private readonly EntityManagerInterface $entity_manager,
    private readonly FlavorRepository $flavor_repository
  ) {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this->setName('catrobat:feature')
      ->addArgument('program_name', InputArgument::REQUIRED, 'Name of program  which gets featured')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $program_name = $input->getArgument('program_name');

    $program = $this->program_manager->findOneByName($program_name);

    if (null === $program) {
      return \Symfony\Component\Console\Command\Command::FAILURE;
    }

    try {
      $this->featureProgram($program);
    } catch (Exception) {
      return \Symfony\Component\Console\Command\Command::INVALID;
    }
    $output->writeln('Featuring '.$program->getName());

    return \Symfony\Component\Console\Command\Command::SUCCESS;
  }

  private function featureProgram(Program $program): void
  {
    $feature = new FeaturedProgram();
    $feature->setProgram($program);
    $feature->setActive(true);
    $feature->setFlavor($this->flavor_repository->getFlavorByName('pocketcode'));
    $feature->setImageType('jpeg'); // todo picture?
    $feature->setUrl(null);

    $source_img = 'public/resources/screenshots/screen_'.$program->getId().'.png';
    $dest_img = 'public/resources/featured/screen_'.$program->getId().'.png';
    copy($source_img, $dest_img);
    $file = new File($dest_img);
    $feature->setNewFeaturedImage($file);

    $this->entity_manager->persist($feature);
    $this->entity_manager->flush();
  }
}
