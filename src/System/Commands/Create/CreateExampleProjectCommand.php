<?php

declare(strict_types=1);

namespace App\System\Commands\Create;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\Special\ExampleProject;
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
class CreateExampleProjectCommand extends Command
{
  public function __construct(
    private readonly ProjectManager $program_manager,
    private readonly EntityManagerInterface $entity_manager,
    private readonly FlavorRepository $flavor_repository,
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

    $project = $this->program_manager->findOneByName($program_name);

    if (!$project instanceof Project) {
      return 1;
    }

    try {
      $this->exampleProgram($project);
    } catch (\Exception $exception) {
      $output->writeln('Failed to example: '.$project->getName().' '.$exception->getMessage());

      return 2;
    }

    $output->writeln('Example: '.$project->getName());

    return 0;
  }

  /**
   * @throws \Exception
   */
  private function exampleProgram(Project $project): void
  {
    $example = new ExampleProject();
    $example->setProject($project);
    $example->setActive(true);
    $example->setFlavor(0 !== random_int(0, 1) ? $this->flavor_repository->getFlavorByName(Flavor::ARDUINO) : $this->flavor_repository->getFlavorByName(Flavor::EMBROIDERY));
    $example->setImageType('jpeg'); // todo picture?
    $example->setForIos(false);

    $source_img = 'public/resources/screenshots/screen_'.$project->getId().'.png';
    $dest_img = 'public/resources/example/screen_'.$project->getId().'.png';
    copy($source_img, $dest_img);
    $file = new File($dest_img);
    $example->setNewExampleImage($file);

    $this->entity_manager->persist($example);
    $this->entity_manager->flush();
  }
}
