<?php
namespace App\Command;

use App\Entity\MessageQueue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of Pong
 *
 * @author lpu8er
 */
class Pong extends Command {

    protected $em = null;
    
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure() {
        $this->setName('discord:msg:pong');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->write('Ping');
        $this->em->getRepository(MessageQueue::class)->register('pong');
        $output->writeln(' pong.');
    }
}
