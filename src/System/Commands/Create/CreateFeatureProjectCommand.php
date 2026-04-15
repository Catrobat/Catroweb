<?php

declare(strict_types=1);

namespace App\System\Commands\Create;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Project;
use App\DB\Entity\Project\Special\FeaturedProject;
use App\DB\EntityRepository\FlavorRepository;
use App\Project\ProjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(name: 'catrobat:feature', description: 'feature a project')]
class CreateFeatureProjectCommand extends Command
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
      ->addArgument('program_name', InputArgument::REQUIRED, 'Name of program  which gets featured')
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
      $this->featureProgram($project);
    } catch (\Exception) {
      return 2;
    }

    $output->writeln('Featuring '.$project->getName());

    return 0;
  }

  private function featureProgram(Project $project): void
  {
    $feature = new FeaturedProject();
    $feature->setProject($project);
    $feature->setActive(true);
    $feature->setFlavor($this->flavor_repository->getFlavorByName(Flavor::POCKETCODE));
    $feature->setImageType('jpeg'); // todo picture?
    $feature->setUrl(null);

    $source_img = 'public/resources/screenshots/screen_'.$project->getId().'.png';
    $dest_img = 'public/resources/featured/screen_'.$project->getId().'.png';
    copy($source_img, $dest_img);
    $file = new File($dest_img);
    $feature->setNewFeaturedImage($file);

    $this->entity_manager->persist($feature);
    $this->entity_manager->flush();
  }
}
