<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\EntityManager;
use Catrobat\AppBundle\Commands\Helpers\ConsoleProgressIndicator;
use Catrobat\AppBundle\Commands\Helpers\CommandHelper;


/**
 * Class CreateNolbUserCommand
 * @package Catrobat\AppBundle\Commands
 */
class CreateNolbUserCommand extends ContainerAwareCommand
{
  /**
   * @var EntityManager
   */
  private $em;

  /**
   * CreateNolbUserCommand constructor.
   *
   * @param EntityManager $em
   */
  public function __construct(EntityManager $em)
  {
    parent::__construct();
    $this->em = $em;
  }

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:nolb-user:create')
      ->setDescription('Creates NOLB user from given file')
      ->addArgument('file', InputArgument::REQUIRED, 'The file to read users.');
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
    $filename = $input->getArgument('file');
    $handle = fopen($filename, "r");
    $indicator = new ConsoleProgressIndicator($output, true);

    if ($handle)
    {
      while (($line = fgets($handle)) !== false)
      {
        $username = CommandHelper::getSubstring($line, " - ", false);
        $password = substr($line, strlen($username) + 2);
        if (CommandHelper::executeShellCommand(
          'php bin/console fos:user:create ' . $username . ' ' . $username . '@nolb ' . $password))
        {
          $user = $this->em->getRepository('AppBundle:User')->findOneBy(['username' => $username]);

          if (!$user)
          {
            $indicator->isFailure();
            $indicator->addError($username);
          }
          else
          {
            $user->setNolbUser(true);
            $this->em->flush();
            $indicator->isSuccess();
          }
        }
        else
        {
          $indicator->isFailure();
          $indicator->addError($username);
        }
      }

      fclose($handle);

      $indicator->printErrors();

    }
    else
    {
      $output->writeln('File not found!');
    }

  }
}
