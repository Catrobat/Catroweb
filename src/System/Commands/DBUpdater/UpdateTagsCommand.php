<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater;

use App\DB\Entity\Project\Tag;
use App\DB\EntityRepository\Project\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:update:tags', description: 'Inserting our static project tags into the Database')]
class UpdateTagsCommand extends Command
{
  final public const TAG_LTM_PREFIX = 'tags.tag.';

  public function __construct(private readonly EntityManagerInterface $entity_manager, private readonly TagRepository $tag_repository)
  {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $count = 0;

    $tag = $this->getOrCreateTag(Tag::GAME)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'game.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $tag = $this->getOrCreateTag(Tag::ANIMATION)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'animation.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $tag = $this->getOrCreateTag(Tag::STORY)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'story.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $tag = $this->getOrCreateTag(Tag::MUSIC)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'music.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $tag = $this->getOrCreateTag(Tag::ART)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'art.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $tag = $this->getOrCreateTag(Tag::EXPERIMENTAL)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'experimental.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $tag = $this->getOrCreateTag(Tag::TUTORIAL)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'tutorial.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $tag = $this->getOrCreateTag(Tag::CODING_JAM_09_2021)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'coding_jam_09_2021.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $this->entity_manager->flush();
    $output->writeln("{$count} Tags in the Database have been inserted/updated");

    return 0;
  }

  protected function getOrCreateTag(string $internal_title): Tag
  {
    $tag = $this->tag_repository->findOneBy(['internal_title' => $internal_title]) ?? new Tag();

    return $tag->setInternalTitle($internal_title);
  }
}
