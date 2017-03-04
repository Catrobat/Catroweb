<?php

namespace Catrobat\AppBundle\Commands\Helpers;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Process\Process;

class CommandHelper
{
    public static function getSubstring($string, $needle, $last_char=false) {
        $pos = strpos($string, $needle);

        if($pos === false) {
            return "";
        }
        if($last_char) {
            $pos = $pos + 1;
        }
        return substr($string, 0, $pos);
    }

    public static function emptyDirectory($directory, $description = "", $output = null)
    {
        if($output)
            $output->write($description . " ('" . $directory . "') ... ");

        if ($directory == '') {
            if($output)
                $output->writeln('failed');
            return false;
        }

        $filesystem = new Filesystem();

        $finder = new Finder();
        $finder->in($directory)->depth(0);
        foreach ($finder as $file) {
            // skip folder in templates directory
            if(($file->getFilename() !== "screenshots") && ($file->getFilename() !== "thumbnails"))
                $filesystem->remove($file);
        }

        if($output)
            $output->writeln('OK');
        return true;
    }

    public static function createDirectory($directory, $description, $output)
    {
        $output->write($description." ('".$directory."') ... ");
        if ($directory == '') {
            $output->writeln('failed');
            return;
        }

        $filesystem = new Filesystem();
        $filesystem->mkdir($directory);

        $output->writeln('OK');
    }

    public static function executeSymfonyCommand($command, $application, $args, $output)
    {
        $command = $application->find($command);
        $args['command'] = $command;
        $input = new ArrayInput($args);
        $command->run($input, $output);
    }

    public static function executeShellCommand($command, $args = array(), $description = "", $output = null) {
        if($output) {
            $output->write($description." ('".$command."') ... ");
        }

        $process = new Process($command);

        if(isset($args['timeout'])) {
            $process->setTimeout($args['timeout']);
        }

        $process->run();

        if ($process->isSuccessful()) {
            if($output)
                $output->writeln('OK');
            return true;
        }

        if($output)
            $output->writeln('failed!');

        return false;
    }
}
