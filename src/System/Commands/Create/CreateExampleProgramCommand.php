<?php

declare(strict_types=1);

namespace App\System\Commands\Create;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\Special\ExampleProgram;
use App\DB\EntityRepository\FlavorRepository;
use App\Project\ProjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(name: 'catrobat:example', description: 'make a example project')]
class CreateExampleProgramCommand extends Command
{
  public function __construct(
    private readonly ProjectManager $program_manager,
    private readonly EntityManagerInterface $entity_manager,
    private readonly FlavorRepository $flavor_repository
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addArgument('program_name', InputArgument::REQUIRED, 'Name of program which gets a example')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $program_name = $input->getArgument('program_name');

    $program = $this->program_manager->findOneByName($program_name);

    if (null === $program) {
      return 1;
    }

    try {
      $this->exampleProgram($program);
    } catch (\Exception $exception) {
      $output->writeln('Failed to example: '.$program->getName().' '.$exception->getMessage());

      return 2;
    }

    $output->writeln('Example: '.$program->getName());

    return 0;
  }

  /**
   * @throws \Exception
   */
  private function exampleProgram(Program $program): void
  {
    $example = new ExampleProgram();
    $example->setProgram($program);
    $example->setActive(true);
    $example->setFlavor(0 !== random_int(0, 1) ? $this->flavor_repository->getFlavorByName(Flavor::ARDUINO) : $this->flavor_repository->getFlavorByName(Flavor::EMBROIDERY));
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
