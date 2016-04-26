<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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
    ->addOption('hard');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('hard')) {
            $output->writeln("This command will reset everything, use with caution! Use '--hard' option if you are sure.");

            return;
        }

        $this->executeShellCommand('php app/console doctrine:schema:drop --force', 'Dropping database', $output);
        $this->executeShellCommand('php app/console doctrine:schema:create', 'Creating database', $output);
        $this->executeShellCommand('php app/console cache:clear --env=test', 'Resetting Cache', $output);

        $this->emptyDirectory($this->getContainer()->getParameter('catrobat.screenshot.dir'), 'Delete screenshots', $output);
        $this->emptyDirectory($this->getContainer()->getParameter('catrobat.thumbnail.dir'),  'Delete thumnails', $output);
        $this->emptyDirectory($this->getContainer()->getParameter('catrobat.file.storage.dir'),  'Delete programs', $output);
        $this->emptyDirectory($this->getContainer()->getParameter('catrobat.file.extract.dir'),  'Delete extracted programs', $output);
        $this->emptyDirectory($this->getContainer()->getParameter('catrobat.featuredimage.dir'),  'Delete featured images', $output);
        $this->emptyDirectory($this->getContainer()->getParameter('catrobat.mediapackage.dir'),  'Delete mediapackages', $output);

        // already happens in doctrine:schema:create
        //$this->executeShellCommand('php app/console init:acl', 'Init ACL', $output);
        $this->executeShellCommand('php app/console sonata:admin:setup-acl', 'Init Sonata admin ACL', $output);
        $this->executeShellCommand('php app/console sonata:admin:generate-object-acl', 'Init Sonata object ACL', $output);

        $this->executeShellCommand('php app/console catrobat:test:generate --env=test', 'Generating test data', $output);
        $this->executeShellCommand('php app/console cache:clear --no-warmup', 'Clearing cache', $output);

        $this->executeShellCommand('php app/console fos:user:create catroweb catroweb@localhost catroweb --super-admin', 'Create default admin user', $output);

        $temp_dir = sys_get_temp_dir().'/catrobat.program.import/';

        $filesystem = new Filesystem();
        $filesystem->remove($temp_dir);
        $filesystem->mkdir($temp_dir);
        $this->downloadPrograms($temp_dir, $output);
        $this->executeShellCommand("php app/console catrobat:import $temp_dir catroweb", 'Importing Projects', $output);
        $this->executeSymfonyCommand('catrobat:import', array('directory' => $temp_dir, 'user' => 'catroweb'), $output);
        $filesystem->remove($temp_dir);
    }

    private function executeShellCommand($command, $description, $output)
    {
        $output->write($description." ('".$command."') ... ");
        $process = new Process($command);
        $process->run();
        if ($process->isSuccessful()) {
            $output->writeln('OK');
        } else {
            $output->writeln('failed!');
        }
    }

    private function emptyDirectory($directory, $description, $output)
    {
        $output->write($description." ('".$directory."') ... ");
        if ($directory == '') {
            $output->writeln('failed');

            return;
        }

        $filesystem = new Filesystem();

        $finder = new Finder();
        $finder->in($directory)->depth(0);
        foreach ($finder as $file) {
            $filesystem->remove($file);
        }
        $output->writeln('OK');
    }

    private function executeSymfonyCommand($command, $args, $output)
    {
        $command = $this->getApplication()->find($command);
        $args['command'] = $command;
        $input = new ArrayInput($args);
        $command->run($input, $output);
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
