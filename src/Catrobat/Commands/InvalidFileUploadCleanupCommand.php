<?php

namespace App\Catrobat\Commands;

use App\Entity\Program;
use App\Repository\ProgramRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class InvalidFileUploadCleanupCommand
 * @package App\Catrobat\Commands
 */
class InvalidFileUploadCleanupCommand extends ContainerAwareCommand
{

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:clean:invalid-upload')
      ->setDescription('Sets all files given in command file to invisible.
      File is just given by name (not path) and has to be located in Commands/invisible_programs')
      ->addArgument('file', InputArgument::REQUIRED, 'File with the programs that terminate with 528 error');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $finder = new Finder();
    $file_name = $input->getArgument('file');
    $finder->files()->name($file_name);
    $folder = $this->getContainer()->getParameter('catrobat.invalidupload.dir');

    $content = '';
    foreach ($finder->in($folder) as $file)
    {
      $content = $file->getContents();
    }
    $ids = explode(",\n", $content);

    /** @var ProgramRepository $pr */
    $pr = $this->getContainer()->get(ProgramRepository::class);

    $fs = new Filesystem();
    /** @var EntityManager $em */
    $em = $this->getContainer()->get('doctrine')->getManager();

    foreach ($ids as $id)
    {
      /** @var Program $program */
      $program = $pr->find($id);
      if (!$program)
      {
        $output->writeln("[Error] Program with id <" . $id . "> doesnt exist! Skipping...");
        continue;
      }
      $program->setVisible(false);
      $output->writeln($program->getName() . ' set to invisible');
      $em->persist($program);
    }
    $em->flush();
    $fs->copy($folder . $file_name, $folder . "/executed/" . date('Y-m-d_H:i:s') . '_done');
    $fs->remove($folder . $file_name);
  }
} 