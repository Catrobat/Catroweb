<?php

declare(strict_types=1);

namespace App\System\Commands\Create;

use App\DB\Entity\User\Notifications\RemixNotification;
use App\Project\Remix\RemixData;
use App\Project\Remix\RemixManager;
use App\System\Commands\Helpers\RemixManipulationProjectManager;
use App\User\Notification\NotificationManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:remix', description: 'add remixes to projects')]
class CreateRemixCommand extends Command
{
  public function __construct(private readonly RemixManipulationProjectManager $remix_manipulation_program_manager,
    private readonly RemixManager $remix_manager,
    private readonly NotificationManager $notification_service)
  {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this
      ->addArgument('program_original', InputArgument::REQUIRED, 'Name of program which gets remixed')
      ->addArgument('program_remix', InputArgument::REQUIRED, 'Names of program which is the remix')
    ;
  }

  /**
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $program_remixes_of_original = [];
    $original_program_name = $input->getArgument('program_original');
    $remix_program_name = $input->getArgument('program_remix');

    if ($original_program_name === $remix_program_name) {
      return 1;
    }

    $program_original = $this->remix_manipulation_program_manager->findOneByName($original_program_name);
    $program_remix = $this->remix_manipulation_program_manager->findOneByName($remix_program_name);

    if (null == $program_original || null == $program_remix) {
      return 2;
    }

    $remix_data_of_original = new RemixData($program_original->getId());
    $program_remixes_of_original[0] = $remix_data_of_original;
    $notification = new RemixNotification($program_original->getUser(), $program_remix->getUser(), $program_original, $program_remix);
    $this->notification_service->addNotification($notification);

    try {
      $this->remix_manager->addRemixes($program_remix, $program_remixes_of_original);
    } catch (\Exception) {
      return 4;
    }
    $output->writeln('Remixing '.$program_original->getName().' with '.$remix_program_name);

    return 0;
  }
}
