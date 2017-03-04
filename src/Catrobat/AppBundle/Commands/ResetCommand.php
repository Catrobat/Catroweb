<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Commands\Helpers\CommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;

class ResetCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('catrobat:reset')
            ->setDescription('Resets everything to base values')
            ->addOption('hard')
            ->addOption('remix-layout', null, InputOption::VALUE_REQUIRED, 'Generates remix graph based on given layout',
                ProgramImportCommand::REMIX_GRAPH_NO_LAYOUT);

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('hard')) {
            $output->writeln("This command will reset everything, use with caution! Use '--hard' option if you are sure.");
            return;
        }

        CommandHelper::executeShellCommand('php app/console doctrine:schema:drop --force', array(), 'Dropping database', $output);

        CommandHelper::executeShellCommand('php app/console catrobat:drop:migration',  array(), 'Dropping the migration_versions table', $output);
        CommandHelper::executeShellCommand('php app/console doctrine:migrations:migrate',  array(), 'Execute the migration to the latest version', $output);
        CommandHelper::executeShellCommand('php app/console catrobat:create:tags',  array(), 'Creating constant tags', $output);
        CommandHelper::executeShellCommand('php app/console cache:clear --env=test',  array('timeout' => 120), 'Resetting Cache', $output);

        CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.screenshot.dir'), 'Delete screenshots', $output);
        CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.thumbnail.dir'),  'Delete thumnails', $output);
        CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.file.storage.dir'),  'Delete programs', $output);
        CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.file.extract.dir'),  'Delete extracted programs', $output);
        CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.featuredimage.dir'),  'Delete featured images', $output);
        CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.mediapackage.dir'),  'Delete mediapackages', $output);
        CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.template.dir'),  'Delete templates', $output);
        CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.template.screenshot.dir'),  'Delete templates-screenshots', $output);
        CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.template.thumbnail.dir'),  'Delete templates-thumbnails', $output);

        CommandHelper::executeShellCommand('php app/console sonata:admin:setup-acl', array(),'Init Sonata admin ACL', $output);
        CommandHelper::executeShellCommand('php app/console sonata:admin:generate-object-acl', array(),'Init Sonata object ACL', $output);

        CommandHelper::executeShellCommand('php app/console catrobat:test:generate --env=test', array(),'Generating test data', $output);
        CommandHelper::executeShellCommand('php app/console cache:clear --no-warmup', array(),'Clearing cache', $output);

        CommandHelper::executeShellCommand('php app/console fos:user:create catroweb catroweb@localhost catroweb --super-admin', array(),'Create default admin user', $output);

        $temp_dir = sys_get_temp_dir().'/catrobat.program.import/';

        $filesystem = new Filesystem();
        $filesystem->remove($temp_dir);
        $filesystem->mkdir($temp_dir);
        $this->downloadPrograms($temp_dir, $output);
        $remix_layout_option = '--remix-layout=' . intval($input->getOption('remix-layout'));
        CommandHelper::executeShellCommand("php app/console catrobat:import $temp_dir catroweb $remix_layout_option",
            array(), 'Importing Projects', $output);
        CommandHelper::executeSymfonyCommand('catrobat:import', $this->getApplication(), array(
            'directory' => $temp_dir,
            'user' => 'catroweb',
            '--remix-layout' => intval($input->getOption('remix-layout'))
        ), $output);
        $filesystem->remove($temp_dir);

        CommandHelper::executeShellCommand('chmod o+w -R web/resources', array(), 'Setting permissions', $output);
    }

    private function downloadPrograms($dir, OutputInterface $output)
    {
        $server_json = json_decode(file_get_contents('https://share.catrob.at/pocketcode/api/projects/recent.json'), true);
        $base_url = $server_json['CatrobatInformation']['BaseUrl'];
        foreach ($server_json['CatrobatProjects'] as $program) {
            $url = $base_url.$program['DownloadUrl'];
            $name = $dir.intval($program['ProjectId']).'.catrobat';
            $output->writeln('Saving <'.$url.'> to <'.$name.'>');
            file_put_contents($name, file_get_contents($url));
        }
    }
}
