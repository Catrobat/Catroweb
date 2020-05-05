<?php

namespace App\Commands\Create;

use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\Services\RemixData;
use App\Commands\Helpers\RemixManipulationProgramManager;
use App\Entity\RemixManager;
use App\Entity\RemixNotification;
use App\Entity\UserManager;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateRemixCommand extends Command
{
  protected static $defaultName = 'catrobat:remix';

  private UserManager $user_manager;

  private RemixManipulationProgramManager $remix_manipulation_program_manager;

  private RemixManager $remix_manager;

  private CatroNotificationService $notification_service;

  public function __construct(UserManager $user_manager,
                              RemixManipulationProgramManager $program_manager,
                              RemixManager $remix_manager,
                              CatroNotificationService $notification_service)
  {
    parent::__construct();
    $this->user_manager = $user_manager;
    $this->remix_manipulation_program_manager = $program_manager;
    $this->remix_manager = $remix_manager;
    $this->notification_service = $notification_service;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:remix')
      ->setDescription('add remixes to projects')
      ->addArgument('program_original', InputArgument::REQUIRED, 'Name of program which gets remixed')
      ->addArgument('program_remix', InputArgument::REQUIRED, 'Names of program which is the remix')
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $original_program_name = $input->getArgument('program_original');
    $remix_program_name = $input->getArgument('program_remix');

    if ($original_program_name === $remix_program_name)
    {
      return 1;
    }

    $program_original = $this->remix_manipulation_program_manager->findOneByName($original_program_name);
    $program_remix = $this->remix_manipulation_program_manager->findOneByName($remix_program_name);

    if (null == $program_original || null == $program_remix)
    {
      return 2;
    }

    $remix_data_of_original = new RemixData($program_original->getId());
    $program_remixes_of_original[0] = $remix_data_of_original;
    $notification = new RemixNotification($program_original->getUser(), $program_remix->getUser(), $program_original, $program_remix);
    $this->notification_service->addNotification($notification);
    if (0 == sizeof($program_remixes_of_original))
    {
      return 3;
    }

    try
    {
      $this->remix_manager->addRemixes($program_remix, $program_remixes_of_original);
    }
    catch (Exception $e)
    {
      return 4;
    }
    $output->writeln('Remixing '.$program_original->getName().' with '.$remix_program_name);

    return 0;
  }
}
