<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\Moderation\ContentModerationAction;
use App\DB\Enum\ContentType;
use App\Moderation\ContentVisibilityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/** Keeps hidden content as evidence: catro:moderation:purge-hidden skips it. A hold on a user covers all their content. */
#[AsCommand(
  name: 'catro:moderation:hold',
  description: 'Keep hidden content as evidence (exclude it from catro:moderation:purge-hidden)',
)]
class HoldContentCommand extends Command
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly ContentVisibilityManager $visibility,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addArgument('type', InputArgument::REQUIRED, 'project, studio, comment or user')
      ->addArgument('id', InputArgument::REQUIRED, 'Content id')
      ->addOption('release', null, InputOption::VALUE_NONE, 'Lift the hold: the content is purged as usual again')
      ->addOption('note', null, InputOption::VALUE_REQUIRED, 'Why, e.g. a case number')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $type = ContentType::tryFrom($input->getArgument('type'));
    if (!$type instanceof ContentType) {
      $io->error('Type must be project, studio, comment or user.');

      return Command::INVALID;
    }

    if (!$this->visibility->contentExists($type, $input->getArgument('id'))) {
      $io->error(sprintf('There is no %s %s.', $type->value, $input->getArgument('id')));

      return Command::FAILURE;
    }

    $release = $input->getOption('release');
    $action = new ContentModerationAction()
      ->setContentType($type->value)
      ->setContentId($input->getArgument('id'))
      ->setAction($release ? ContentModerationAction::ACTION_EVIDENCE_RELEASED : ContentModerationAction::ACTION_EVIDENCE_HOLD)
      ->setNote($input->getOption('note'))
    ;
    $this->entity_manager->persist($action);
    $this->entity_manager->flush();

    $io->success(sprintf('%s %s %s.', $release ? 'Released' : 'Holding', $type->value, $input->getArgument('id')));

    return Command::SUCCESS;
  }
}
