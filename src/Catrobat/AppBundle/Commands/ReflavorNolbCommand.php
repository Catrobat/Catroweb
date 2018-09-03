<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Commands\Helpers\ConsoleProgressIndicator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ReflavorNolbCommand extends ContainerAwareCommand
{
  private $em;
  private $program_repository;

  public function __construct(EntityManager $em, $program_repo)
  {
    parent::__construct();
    $this->em = $em;
    $this->program_repository = $program_repo;
  }

  protected function configure()
  {
    $this->setName('catrobat:reflavor:nolb')
      ->setDescription('Reflavor programs from nolb users')
      ->addArgument('flavor', InputArgument::REQUIRED, 'Flavor');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    /*
     * @var $program Program
     */
    $flavor = $input->getArgument('flavor');

    $offset = 0;
    $limit = 20;
    $programs = $this->program_repository->getProgramsFromNolbUser($limit, $offset);
    $count = count($programs);

    $progress_indicator = new ConsoleProgressIndicator($output);

    for ($index = 1; $count != 0; $index += 1)
    {
      foreach ($programs as $program)
      {
        $program->setFlavor($flavor);
        $this->em->persist($program);
        $progress_indicator->isSuccess();
      }

      $this->em->flush();

      $offset = $index * $limit;
      $programs = $this->program_repository->getProgramsFromNolbUser($limit, $offset);
      $count = count($programs);
    }

    $output->writeln('');
    $output->writeln('done.');
  }
}
