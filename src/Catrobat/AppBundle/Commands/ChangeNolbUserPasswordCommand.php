<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Doctrine\ORM\EntityManager;
use Catrobat\AppBundle\Entity\UserManager;

class ChangeNolbUserPasswordCommand extends ContainerAwareCommand
{
    private $em;

    public function __construct(EntityManager $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setName('catrobat:nolb-user:change-password')
            ->setDescription('Changes password from given nolb users')
            ->addArgument('file', InputArgument::REQUIRED, 'The file to read users.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('file');
        $handle = fopen($filename, "r");
        $fail_array = [];
        $inline_count = 0;
        $line_count = 1;
        $line_length = 80;

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $username = $this->getSubstring($line, " - ", false);
                $password = substr($line, strlen($username) + 2);

                $user = $this->em->getRepository('AppBundle:User')->findOneBy(array('username' => $username));

                if (!$user || !$user->getNolbUser()) {
                    array_push($fail_array, $username);
                    $output->write('<error>F</error>');
                }
                else {
                    if($this->executeShellCommand('php app/console fos:user:change-password '.$username.' '.$password)) {
                        $output->write('<info>.</info>');
                    }
                    else {
                        array_push($fail_array, $username);
                        $output->write('<error>F</error>');
                    }
                }
                $inline_count += 1;

                if($inline_count >= $line_length) {
                    $number = $line_length * $line_count;
                    $output->writeln(' '.$number);
                    $inline_count = 0;
                    $line_count += 1;
                }
            }
            fclose($handle);
            $this->printErrors($output, $fail_array);
        } else {
            $output->writeln('File not found!');
        }

    }

    private function getSubstring($string, $needle, $last_char=false) {
        $pos = strpos($string, $needle);

        if($pos === false) {
            return "";
        }
        if($last_char) {
            $pos = $pos + 1;
        }
        return substr($string, 0, $pos);
    }

    private function executeShellCommand($command)
    {
        $process = new Process($command);
        $process->run();
        if ($process->isSuccessful()) {
            return true;
        } else {
            return false;
        }
    }

    private function printErrors($output, $errors) {

        $output->writeln('');
        $error_count = count($errors);
        if($error_count == 0) {
            $output->writeln('Successfully executed command. There were no errors.');
        }
        else {
            $output->writeln('Command executed. There were '.$error_count.' errors.');
            $output->writeln('Errors happened with the following users:');

            for($i = 0; $i < $error_count; $i++) {
                if ($i >= 50) {
                    $output->writeln('...');
                    break;
                }
                if (($i + 1) != $error_count) {
                    $output->write($errors[$i].', ');
                }
                else {
                    $output->writeln($errors[$i]);
                }
            }
        }
    }
}
