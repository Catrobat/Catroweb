<?php

namespace App\System\Commands\Create;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Studios\StudioActivityRepository;
use App\DB\EntityRepository\Studios\StudioProgramRepository;
use App\DB\EntityRepository\Studios\StudioRepository;
use App\DB\EntityRepository\Studios\StudioUserRepository;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use App\Studio\StudioManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class CreateStudioCommand  extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StudioManager $studioManager,
        private readonly UserManager $user_manager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('catrobat:studio')
            ->addArgument('name',InputArgument::REQUIRED,'Name for the Studio')
            ->addArgument('user', InputArgument::REQUIRED, 'User which is admin on a studio')
            ->addArgument('is_public', InputArgument::REQUIRED, 'Sets studio to public or private')
            ->addArgument('is_enabled', InputArgument::REQUIRED, 'Enables studio')
            ->addArgument('allow_comments', InputArgument::REQUIRED, 'Enables comments in studios')
            ->addArgument('description', InputArgument::REQUIRED, 'Description for the studio')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('user');
        $name = $input->getArgument('name');
        $description = $input->getArgument('description');
        $isPublic = filter_var($input->getArgument('is_public'), FILTER_VALIDATE_BOOLEAN);
        $isEnabled = filter_var($input->getArgument('is_enabled'), FILTER_VALIDATE_BOOLEAN);
        $allowComments = filter_var($input->getArgument('allow_comments'), FILTER_VALIDATE_BOOLEAN);
        $coverPath = null;

        /** @var User|null $user */
        $user = $this->user_manager->findUserByUsername($username);

        if (null === $user) {
            $output->writeln('User not found.');

            return 1;
        }

        try {
            $this->createStudio($user,$name, $description, $isPublic, $isEnabled, $allowComments, $coverPath);
            $output->writeln('Studio created successfully.');
            return 0;
        } catch (\Exception $e) {
            $output->writeln('Failed to create studio: ' . $e->getMessage());

            return 2;
        }


    }

   private function createStudio(User $user, string $name, string $description, bool $is_public , bool $is_enabled , bool $allow_comments, string $cover_path = null):void
    {

        $studio = (new Studio())
            ->setName($name)
            ->setDescription($description)
            ->setIsPublic($is_public)
            ->setIsEnabled($is_enabled)
            ->setAllowComments($allow_comments)
            ->setCoverPath($cover_path)
            ->setCreatedOn(new \DateTime())
        ;

        $this->entityManager->persist($studio);
        $this->entityManager->flush();
        $this->entityManager->refresh($studio);
        $this->studioManager->addAdminToStudio($user, $studio);

    }
}