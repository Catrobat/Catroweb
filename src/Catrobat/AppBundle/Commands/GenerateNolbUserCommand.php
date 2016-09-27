<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\EntityManager;

class GenerateNolbUserCommand extends ContainerAwareCommand
{
    private $em;
    private $charset = "abcdefghijklmnopqrstuvwxyz";
    private $password_length = 6;

    public function __construct(EntityManager $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setName('catrobat:nolb-user:generate')
            ->setDescription('Generate NOLB user file with given arguments')
            ->addArgument('identifier', InputArgument::REQUIRED, 'The start from username')
            ->addArgument('start', InputArgument::REQUIRED, 'Number where to start')
            ->addArgument('end', InputArgument::REQUIRED, 'Number where to stop');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('identifier');
        $start = $input->getArgument('start');
        $end = $input->getArgument('end');

        $output_file = fopen($identifier.'.txt', 'w');

        for(; $start <= $end; $start++) {
            $password = substr(str_shuffle(str_repeat($this->charset, $this->password_length)), 0, $this->password_length);
            $line_str = $identifier.str_pad($start, 4, '0', STR_PAD_LEFT).' - '.$password;
            fwrite($output_file, $line_str."\n");
        }

        fclose($output_file);

        $output->writeln('File successfully created.');
    }
}
