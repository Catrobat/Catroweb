<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Commands\Helpers\CommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;

class WebShareProgramImport extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('catrobat:import:webshare')
             ->setDescription('Imports the specified amount of recent programs from share.catrob.at')
             ->addArgument('amount', InputArgument::REQUIRED, 'The amount of recent programs that should be downloaded and imported');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $amount = $input->getArgument('amount');
        $temp_dir = sys_get_temp_dir().'/catrobat.program.import/';

        $filesystem = new Filesystem();
        $filesystem->remove($temp_dir);
        $filesystem->mkdir($temp_dir);
        $this->downloadPrograms($temp_dir, $output, $amount);
        CommandHelper::executeShellCommand("php app/console catrobat:import $temp_dir catroweb", array(), 'Importing Projects', $output);
        CommandHelper::executeSymfonyCommand('catrobat:import', $this->getApplication(), array('directory' => $temp_dir, 'user' => 'catroweb'), $output);
        $filesystem->remove($temp_dir);
    }

    private function downloadPrograms($dir, OutputInterface $output, $limit=20)
    {
        $server_json = json_decode(file_get_contents('https://share.catrob.at/pocketcode/api/projects/recent.json?limit='.$limit), true);
        $base_url = $server_json['CatrobatInformation']['BaseUrl'];
        foreach ($server_json['CatrobatProjects'] as $program) {
            $url = $base_url.$program['DownloadUrl'];
            $name = $dir.intval($program['ProjectId']).'.catrobat';
            $output->writeln('Saving <'.$url.'> to <'.$name.'>');
            file_put_contents($name, file_get_contents($url));
        }
    }
}
