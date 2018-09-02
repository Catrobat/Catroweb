<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Doctrine\ORM\EntityManager;
use Catrobat\AppBundle\Commands\Helpers\ConsoleProgressIndicator;
use Catrobat\AppBundle\Commands\Helpers\CommandHelper;

class CreateNolbUserCommand extends ContainerAwareCommand
{
  private $em;

  public function __construct(EntityManager $em)
  {
    parent::__construct();
    $this->em = $em;
  }

  protected function configure()
  {
    $this->setName('catrobat:nolb-user:create')
      ->setDescription('Creates NOLB user from given file')
      ->addArgument('file', InputArgument::REQUIRED, 'The file to read users.');
  }

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
        if (CommandHelper::executeShellCommand('php app/console fos:user:create ' . $username . ' ' . $username . '@nolb ' . $password))
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
