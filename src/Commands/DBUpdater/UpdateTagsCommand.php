<?php

namespace App\Commands\DBUpdater;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UpdateTagsCommand extends Command
{
  protected static $defaultName = 'catrobat:create:tags';
  private OutputInterface $output;

  private TranslatorInterface $translator;

  private TagRepository $tag_repository;

  private EntityManagerInterface $entity_manager;

  public function __construct(EntityManagerInterface $entity_manager,TranslatorInterface $translator,
                              TagRepository $tag_repository)
  {
    parent::__construct();
    $this->entity_manager = $entity_manager;
    $this->translator = $translator;
    $this->tag_repository = $tag_repository;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:create:tags')
      ->setDescription('Creating constant tags in supported languages')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->output = $output;
    $metadata = $this->entity_manager->getClassMetadata('App\Entity\Tag')->getFieldNames();

    $number_of_tags = 7; // uses the tag names defined in the translation files!

    for ($i = 1; $i <= $number_of_tags; ++$i) {
      $tag = $this->tag_repository->find($i);

      if (null != $tag) {
        for ($j = 1; $j < count($metadata); ++$j) {
          $language = 'set'.$metadata[$j];

          $tag->{$language}($this->trans('tags.constant.tag'.$i, $metadata[$j]));

          $this->entity_manager->persist($tag);
          $this->entity_manager->flush();
        }
      } else {
        $tag = new Tag();

        for ($j = 1; $j < count($metadata); ++$j) {
          $language = 'set'.$metadata[$j];
          $tag->{$language}($this->trans('tags.constant.tag'.$i, $metadata[$j]));
        }

        $this->entity_manager->persist($tag);
        $this->entity_manager->flush();
      }
    }

    return 0;
  }

  /**
   * @param mixed $message
   * @param mixed $locale
   */
  private function trans($message, $locale): string
  {
    $parameters = [];

    return $this->translator->trans($message, $parameters, 'catroweb', $locale);
  }
}
