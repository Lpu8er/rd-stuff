<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Description of CreateUser
 *
 * @author lpu8er
 */
class CreateUser extends Command {
    protected $em = null;
    protected $encoder = null;
    
    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder) {
        $this->em = $em;
        $this->encoder = $encoder;
        parent::__construct();
    }
    
    protected function configure() {
        $this->setName('rd:createuser')
                ->addArgument('email', InputArgument::REQUIRED)
                ->addArgument('pwd', InputArgument::REQUIRED);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $e = $input->getArgument('email');
        $p = $input->getArgument('pwd');
        $u = new User;
        $u->setDisplayName($e);
        $u->setEmail($e);
        $u->setRoles(['ROLE_USER',]);
        $u->setPassword($this->encoder->encodePassword($u, $p));
        $this->em->persist($u);
        $this->em->flush();
    }
}
