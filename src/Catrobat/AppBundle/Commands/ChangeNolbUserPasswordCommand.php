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
 * Class ChangeNolbUserPasswordCommand
 * @package Catrobat\AppBundle\Commands
 */
class ChangeNolbUserPasswordCommand extends ContainerAwareCommand
{
  /**
   * @var EntityManager
   */
  private $em;

  /**
   * ChangeNolbUserPasswordCommand constructor.
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
    $this->setName('catrobat:nolb-user:change-password')
      ->setDescription('Changes password from given nolb users')
      ->addArgument('file', InputArgument::REQUIRED, 'The file to read users.');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
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

        $user = $this->em->getRepository('AppBundle:User')->findOneBy(['username' => $username]);

        if (!$user || !$user->getNolbUser())
        {
          $indicator->isFailure();
          $indicator->addError($username);
        }
        else
        {
          if (CommandHelper::executeShellCommand('php bin/console fos:user:change-password ' . $username . ' ' . $password))
          {
            $indicator->isSuccess();
          }
          else
          {
            $indicator->isFailure();
            $indicator->addError($username);
          }
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
