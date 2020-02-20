<?php

namespace App\Catrobat\Commands;

use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Tag;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CreateConstantTagsCommand
 * @package App\Catrobat\Commands
 */
class CreateConstantTagsCommand extends Command
{

  /**
   * @var
   */
  private $output;

  /**
   * @var TranslatorInterface
   */
  private $translator;

  /**
   * @var TagRepository $tag_repository
   */
  private $tag_repository;

  /**
   * @var EntityManagerInterface
   */
  private $entity_manager;

  /**
   * CreateConstantTagsCommand constructor.
   *
   * @param EntityManagerInterface $entity_manager
   * @param TranslatorInterface $translator
   * @param TagRepository $tag_repository
   */
  public function __construct(EntityManagerInterface $entity_manager,TranslatorInterface $translator,
                              TagRepository $tag_repository)
  {
    parent::__construct();
    $this->entity_manager = $entity_manager;
    $this->translator = $translator;
    $this->tag_repository = $tag_repository;
  }

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:create:tags')
      ->setDescription('Creating constant tags in supported languages');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;
    $metadata = $this->entity_manager->getClassMetadata('App\Entity\Tag')->getFieldNames();

    $number_of_tags = 7; // uses the tag names defined in the translation files!

    for ($i = 1; $i <= $number_of_tags; $i++)
    {
      $tag = $this->tag_repository->find($i);

      if ($tag != null)
      {

        for ($j = 1; $j < count($metadata); $j++)
        {
          $language = 'set' . $metadata[$j];

          $tag->$language($this->trans('tags.constant.tag' . $i, $metadata[$j]));

          $this->entity_manager->persist($tag);;
          $this->entity_manager->flush();
        }

      }
      else
      {
        $tag = new Tag();

        for ($j = 1; $j < count($metadata); $j++)
        {
          $language = 'set' . $metadata[$j];
          $tag->$language($this->trans('tags.constant.tag' . $i, $metadata[$j]));
        }

        $this->entity_manager->persist($tag);;
        $this->entity_manager->flush();
      }
    }
  }

  /**
   * @param $message
   * @param $locale
   *
   * @return string
   */
  private function trans($message, $locale)
  {
    $parameters = [];

    return $this->translator->trans($message, $parameters, 'catroweb', $locale);
  }
}
