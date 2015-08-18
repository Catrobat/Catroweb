<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Services\ApkRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanApkCommand extends ContainerAwareCommand
{
  private $apk_repository;
  private $program_manager;
  private $output;

  public function __construct(ApkRepository $apk_repository, ProgramManager $program_manager)
  {
    parent::__construct();
    $this->apk_repository = $apk_repository;
    $this->program_manager = $program_manager;
  }

  protected function configure()
  {
    $this->setName('catrobat:clean:apk')
         ->setDescription('Delete APKs with status ready');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;
    $programs = $this->program_manager->getProgramsWithApkStatus(Program::APK_READY);
    $this->output->writeln('There are ' . count($programs) . ' APKs to delete!');

    foreach ($programs as $program)
    {
      /** @var Program $program */
      $this->apk_repository->remove($program->getId());
      $program->setApkStatus(Program::APK_NONE);
      $this->program_manager->save($program);
    }

    $this->output->writeln('All APKs deleted!');
  }
} 