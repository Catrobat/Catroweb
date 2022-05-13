<?php

namespace App\System\Commands\Create;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Special\ExampleProgram;
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

class CreateExampleProgramCommand extends Command
{
  protected static $defaultName = 'catrobat:example';

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
    $this->setName('catrobat:example')
      ->setDescription('make a example project')
      ->addArgument('program_name', InputArgument::REQUIRED, 'Name of program which gets a example')
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
      $this->exampleProgram($program);
    } catch (Exception $e) {
      $output->writeln('Failed to example: '.$program->getName().' '.$e->getMessage());

      return 2;
    }
    $output->writeln('Example: '.$program->getName());

    return 0;
  }

  /**
   * @throws Exception
   */
  private function exampleProgram(Program $program): void
  {
    $example = new ExampleProgram();
    $example->setProgram($program);
    $example->setActive(true);
    $example->setFlavor(random_int(0, 1) ? $this->flavor_repository->getFlavorByName('arduino') : $this->flavor_repository->getFlavorByName('embroidery'));
    $example->setImageType('jpeg'); // todo picture?
    $example->setForIos(false);

    $source_img = 'public/resources/screenshots/screen_'.$program->getId().'.png';
    $dest_img = 'public/resources/example/screen_'.$program->getId().'.png';
    copy($source_img, $dest_img);
    $file = new File($dest_img);
    $example->setNewExampleImage($file);

    $this->entity_manager->persist($example);
    $this->entity_manager->flush();
  }
}
