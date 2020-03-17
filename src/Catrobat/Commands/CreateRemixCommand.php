<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\RemixManipulationProgramManager;
use App\Catrobat\Commands\Helpers\ResetController;
use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\Services\RemixData;
use App\Entity\RemixManager;
use App\Entity\RemixNotification;
use App\Entity\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateRemixCommand extends Command
{
  /**
   * @var UserManager
   */
  private $user_manager;

  /**
   * @var RemixManipulationProgramManager
   */
  private $remix_manipulation_program_manager;

  /**
   * @var ResetController
   */
  private $reset_controller;

  /**
   * @var RemixManager
   */
  private $remix_manager;

  /**
   * @var CatroNotificationService
   */
  private $notification_service;

  /**
   * CreateDownloadsCommand constructor.
   */
  public function __construct(UserManager $user_manager,
                              RemixManipulationProgramManager $program_manager,
                              ResetController $reset_controller,
                              RemixManager $remix_manager,
                              CatroNotificationService $notification_service)
  {
    parent::__construct();
    $this->user_manager = $user_manager;
    $this->remix_manipulation_program_manager = $program_manager;
    $this->reset_controller = $reset_controller;
    $this->remix_manager = $remix_manager;
    $this->notification_service = $notification_service;
  }

  protected function configure()
  {
    $this->setName('catrobat:remix')
      ->setDescription('add remixes to projects')
      ->addArgument('program_original', InputArgument::REQUIRED, 'Name of program which gets remixed')
      ->addArgument('program_remix', InputArgument::REQUIRED, 'Names of program which is the remix')
    ;
  }

  /**
   * @throws \Exception
   *
   * @return int|void|null
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $original_program_name = $input->getArgument('program_original');
    $remix_program_name = $input->getArgument('program_remix');

    $program_original = $this->remix_manipulation_program_manager->findOneByName($original_program_name);
    $program_remix = $this->remix_manipulation_program_manager->findOneByName($remix_program_name);
    if ($original_program_name === $remix_program_name)
    {
      return;
    }

    $remix_data_of_original = new RemixData($program_original->getId());
    $program_remixes_of_original[0] = $remix_data_of_original;
    $notification = new RemixNotification($program_original->getUser(), $program_remix->getUser(), $program_original, $program_remix);
    $this->notification_service->addNotification($notification);

    if (null == $program_original || 0 == sizeof($program_remixes_of_original) || null == $this->reset_controller)
    {
      return;
    }

    try
    {
      $this->remix_manager->addRemixes($program_remix, $program_remixes_of_original);
    }
    catch (\Exception $e)
    {
      return;
    }
    $output->writeln('Remixing '.$program_original->getName().' with '.$remix_program_name);
  }
}
