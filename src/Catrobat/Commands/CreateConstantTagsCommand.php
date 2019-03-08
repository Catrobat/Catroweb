<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Services\CatrobatFileExtractor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use App\Entity\ProgramManager;
use App\Entity\UserManager;
use App\Entity\Tag;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Process\Process;
use App\Entity\Program;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use App\Entity\FeaturedProgram;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Translation\TranslatorInterface;
use App\Repository\TagRepository;

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
   * @var EntityManager
   */
  private $em;

  /**
   * CreateConstantTagsCommand constructor.
   *
   * @param EntityManager       $em
   * @param TranslatorInterface $translator
   */
  public function __construct(EntityManager $em, TranslatorInterface $translator)
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
    $this->tag_repository = $this->getContainer()->get('tagrepository');
    $metadata = $this->em->getClassMetadata('App\Entity\Tag')->getFieldNames();

    for ($i = 1; $i <= 6; $i++)
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
