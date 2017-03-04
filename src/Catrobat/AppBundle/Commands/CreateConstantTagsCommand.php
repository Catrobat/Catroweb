<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Services\CatrobatFileExtractor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Entity\Tag;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Process\Process;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Catrobat\AppBundle\Entity\FeaturedProgram;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Translation\TranslatorInterface;
use Catrobat\AppBundle\Entity\TagRepository;

class CreateConstantTagsCommand extends ContainerAwareCommand
{

    private $output;
    private $translator;
    private $tag_repository;

    public function __construct(EntityManager $em, TranslatorInterface $translator)
    {
        parent::__construct();
        $this->em = $em;
        $this->translator = $translator;
    }

    protected function configure()
    {
        $this->setName('catrobat:create:tags')
            ->setDescription('Creating constant tags in supported languages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->tag_repository = $this->getContainer()->get('tagrepository');
        $metadata = $this->em->getClassMetadata('Catrobat\AppBundle\Entity\Tag')->getFieldNames();

        for($i = 1; $i <= 6; $i++ ) {
            $tag = $this->tag_repository->find($i);

            if($tag != null) {

                for($j = 1; $j < count($metadata); $j++) {
                    $language = 'set'.$metadata[$j];

                    $tag->$language($this->trans('tags.constant.tag' . $i, $metadata[$j]));

                    $this->em->persist($tag);;
                    $this->em->flush();
                }

            } else {
                
                $tag = new Tag();

                for($j = 1; $j < count($metadata); $j++) {
                    $language = 'set'.$metadata[$j];
                    $tag->$language($this->trans('tags.constant.tag' . $i, $metadata[$j]));
                }

                $this->em->persist($tag);;
                $this->em->flush();
            }
        }
    }

    private function trans($message, $locale)
    {
        $parameters = array();
        return $this->translator->trans($message, $parameters, 'catroweb', $locale);
    }
}
