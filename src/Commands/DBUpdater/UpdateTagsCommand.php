<?php

namespace App\Commands\DBUpdater;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateTagsCommand extends Command
{
  /**
   * @var string|null
   *
   * @override from Command
   */
  protected static $defaultName = 'catrobat:update:tags';

  private TagRepository $tag_repository;
  private EntityManagerInterface $entity_manager;

  public const TAG_LTM_PREFIX = 'tags.tag.';

  public function __construct(EntityManagerInterface $entity_manager, TagRepository $tag_repository)
  {
    parent::__construct();
    $this->entity_manager = $entity_manager;
    $this->tag_repository = $tag_repository;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:update:tags')
      ->setDescription('Inserting our static project tags into the Database')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $count = 0;

    $tag = $this->getOrCreateTag(Tag::GAME, 1)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'game.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $tag = $this->getOrCreateTag(Tag::ANIMATION, 2)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'animation.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $tag = $this->getOrCreateTag(Tag::STORY, 3)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'story.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $tag = $this->getOrCreateTag(Tag::MUSIC, 4)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'music.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $tag = $this->getOrCreateTag(Tag::ART, 5)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'art.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $tag = $this->getOrCreateTag(Tag::EXPERIMENTAL, 6)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'experimental.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $tag = $this->getOrCreateTag(Tag::TUTORIAL, 7)
      ->setTitleLtmCode(self::TAG_LTM_PREFIX.'tutorial.title')
      ->setEnabled(true)
    ;
    ++$count;
    $this->entity_manager->persist($tag);

    $this->entity_manager->flush();
    $output->writeln("{$count} Tags in the Database have been inserted/updated");

    return 0;
  }

  /**
   * ToDo: id is deprecated -- remove once transition was made.
   */
  protected function getOrCreateTag(string $internal_title, int $id = 0): Tag
  {
    $tag = $this->tag_repository->findOneBy(['internal_title' => $internal_title]);
    if (is_null($tag)) {
      $tag = $this->tag_repository->findOneBy(['id' => $id]) ?? new Tag();
    }

    return $tag->setInternalTitle($internal_title);
  }
}
