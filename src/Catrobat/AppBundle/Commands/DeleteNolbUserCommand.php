<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Commands\Helpers\ConsoleProgressIndicator;
use Catrobat\AppBundle\Commands\Helpers\CommandHelper;


/**
 * Class DeleteNolbUserCommand
 * @package Catrobat\AppBundle\Commands
 */
class DeleteNolbUserCommand extends ContainerAwareCommand
{
  /**
   * @var UserManager
   */
  private $user_manager;

  /**
   * DeleteNolbUserCommand constructor.
   *
   * @param UserManager $user_manager
   */
  public function __construct(UserManager $user_manager)
  {
    parent::__construct();
    $this->user_manager = $user_manager;
  }

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:nolb-user:delete')
      ->setDescription('Deletes NOLB user from given file')
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
        $user = $this->user_manager->findOneBy(['username' => $username]);

        if (!$user)
        {
          $indicator->isFailure();
          $indicator->addError($username);
        }
        else
        {
          $this->user_manager->delete($user);
          $indicator->isSuccess();
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
