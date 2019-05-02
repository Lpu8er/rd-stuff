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
                ->addArgument('words', InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->write('Registering... ');
        $ws = $input->getArgument('words');
        foreach($ws as $w) {
            $output->write('.');
            $word = new Word;
            $word->setWord(strtoupper($w));
            $this->em->persist($word);
        }
        $this->em->flush();
        $output->writeln(' done.');
    }
}
