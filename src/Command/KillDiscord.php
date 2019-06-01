<?php
namespace App\Command;

use App\Entity\Hangman;
use App\Entity\MessageQueue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of KillDiscord
 *
 * @author lpu8er
 */
class KillDiscord extends Command {

    protected $em = null;
    
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure() {
        $this->setName('discord:kill');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->write('Killing');
        $this->em->getRepository(MessageQueue::class)->register('kill');
        $this->cleanup();
        $output->writeln(' killed.');
    }
    
    protected function cleanup() {
        $hs = $this->em->getRepository(Hangman::class)->findAll();
        foreach($hs as $h) {
            $this->em->persist($h);
            $this->em->flush();
        }
    }
}
