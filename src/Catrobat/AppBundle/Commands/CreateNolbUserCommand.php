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

class CreateNolbUserCommand extends ContainerAwareCommand
{
    private $em;

    public function __construct(EntityManager $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setName('catrobat:nolb-user')
            ->setDescription('Creates NOLB user from given file')
            ->addArgument('file', InputArgument::REQUIRED, 'The file to read users.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('file');
        $handle = fopen($filename, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $username = $this->getSubstring($line, " - ", false);
                $password = substr($line, strlen($username) + 2);
                if($this->executeShellCommand('php app/console fos:user:create '.$username.' '.$username.'@nolb '.$password)) {
                    $user = $this->em->getRepository('AppBundle:User')->findOneBy(array('username' => $username));

                    if (!$user) {
                        $output->writeln('User ' . $username . ' not found');
                    }
                    else {
                        $user->setNolbUser(true);
                        $this->em->flush();
                    }
                }
            }

            fclose($handle);
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
}
