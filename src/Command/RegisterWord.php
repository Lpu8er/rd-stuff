<?php

namespace App\Command;

use App\Entity\Word;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of RegisterWord
 *
 * @author lpu8er
 */
class RegisterWord extends Command {

    protected $em = null;
    
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure() {
        $this->setName('discord:word:register')
                ->addArgument('word', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->write('Registering...');
        $w = $input->getArgument('word');
        $word = new Word;
        $word->setWord(strtoupper($w));
        $this->em->persist($word);
        $this->em->flush();
        $output->writeln(' done.');
    }
}
