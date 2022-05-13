<?php

namespace App\System\Commands\Create;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Special\FeaturedProgram;
use App\DB\EntityRepository\FlavorRepository;
use App\Project\ProgramManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;

class CreateFeatureProgramCommand extends Command
{
  protected static $defaultName = 'catrobat:feature';

  private UserManager $user_manager;

  private ProgramManager $program_manager;

  private EntityManagerInterface $entity_manager;

  private FlavorRepository $flavor_repository;

  public function __construct(UserManager $user_manager, ProgramManager $program_manager,
                              EntityManagerInterface $entity_manager, FlavorRepository $flavor_repository)
  {
    parent::__construct();
    $this->user_manager = $user_manager;
    $this->program_manager = $program_manager;
    $this->entity_manager = $entity_manager;
    $this->flavor_repository = $flavor_repository;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:feature')
      ->setDescription('feature a project')
      ->addArgument('program_name', InputArgument::REQUIRED, 'Name of program  which gets featured')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $program_name = $input->getArgument('program_name');

    $program = $this->program_manager->findOneByName($program_name);

    if (null === $program) {
      return 1;
    }

    try {
      $this->featureProgram($program);
    } catch (Exception $e) {
      return 2;
    }
    $output->writeln('Featuring '.$program->getName());

    return 0;
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
