<?php

namespace App\Catrobat\Commands;

use App\Repository\ProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class InvalidFileUploadCleanupRevertCommand extends Command
{
  protected static $defaultName = 'catrobat:clean:invalid-upload:revert';
  private EntityManagerInterface $entity_manager;

  private ParameterBagInterface $parameter_bag;

  private ProgramRepository $program_repository;

  public function __construct(EntityManagerInterface $entity_manager, ParameterBagInterface $parameter_bag,
                              ProgramRepository $program_repository)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
    $this->entity_manager = $entity_manager;
    $this->program_repository = $program_repository;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:clean:invalid-upload:revert')
      ->setDescription('Sets all files given in command file to visible.
      File is just given by name (not path) and has to be located in Commands/invisible_programs')
      ->addArgument('file', InputArgument::REQUIRED, 'File with the programs that should be visible again')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $finder = new Finder();
    $file_name = $input->getArgument('file');
    $finder->files()->name($file_name);
    $folder = $this->parameter_bag->get('catrobat.invalidupload.dir');

    $content = '';

    /** @var File $file */
    foreach ($finder->in($folder) as $file)
    {
      $content = file_get_contents($file);
    }
    $ids = explode(",\n", $content);

    $fs = new Filesystem();

    foreach ($ids as $id)
    {
      $program = $this->program_repository->find($id);
      if (!$program)
      {
        $output->writeln('[Error] Program with id <'.$id.'> doesnt exist! Skipping...');
        continue;
      }
      $program->setVisible(true);
      $output->writeln($program->getName().' set to visible');
      $this->entity_manager->persist($program);
    }
    $this->entity_manager->flush();
    $fs->copy($folder.$file_name, $folder.'/executed/'.date('Y-m-d_H:i:s').'_revert');
    $fs->remove($folder.$file_name);

    return 0;
  }
}
