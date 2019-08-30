<?php

namespace App\Catrobat\Commands;

use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CreateConstantTagsCommand
 * @package App\Catrobat\Commands
 */
class CreateConstantTagsCommand extends ContainerAwareCommand
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
   * @var
   */
  private $tag_repository;

  /**
   * @var EntityManagerInterface
   */
  private $em;

  /**
   * CreateConstantTagsCommand constructor.
   *
   * @param EntityManagerInterface       $em
   * @param TranslatorInterface $translator
   */
  public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
  {
    parent::__construct();
    $this->em = $em;
    $this->translator = $translator;
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
    $this->tag_repository = $this->getContainer()->get(TagRepository::class);
    $metadata = $this->em->getClassMetadata('App\Entity\Tag')->getFieldNames();

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

          $this->em->persist($tag);;
          $this->em->flush();
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

        $this->em->persist($tag);;
        $this->em->flush();
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
