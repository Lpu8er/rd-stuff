<?php

namespace App\Repository;

use App\Entity\Hangman;
use App\Entity\Word;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Hangman|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hangman|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hangman[]    findAll()
 * @method Hangman[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HangmanRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Hangman::class);
    }
    
    public function getCurrent(): ?Hangman {
        $returns = null;
        $hs = $this->findAll();
        if(!empty($hs)) {
            $returns = $hs[0];
        }
        return $returns;
    }
    
    public function generate() {
        // doctrine cannot select randomly for obvious reasons, so let's be "fun"
        $words = $this->_em->getRepository(Word::class)->findAll();
        $word = $words[array_rand($words)];
        $w = $word->getWord();
        $h = new Hangman;
        $h->setTries(0);
        $h->setLetters('');
        $h->setWord($w);
        $h->setDiscovered(preg_replace('`[a-zA-Z]`', '-', $w));
        $this->_em->persist($h);
        $this->_em->flush();
        return $h;
    }
    
    public function findOrCreate(): Hangman {
        $h = $this->getCurrent();
        if(empty($h)) {
            $h = $this->generate();
        }
        return $h;
    }
    
    public function blast(Hangman $h) {
        $this->_em->remove($h);
        $this->_em->flush();
    }
    
    public function testLetter(Hangman $h, string $letter): int {
        $returns = 0;
        $letter = substr(strtoupper($letter), 0, 1);
        $x = str_split($h->getLetters());
        $y = str_split($h->getWord());
        if(in_array($letter, $x)) {
            $returns += Hangman::LETTER_ALREADY_TRIED;
        } else {
            if(in_array($letter, $y)) {
                $returns += Hangman::LETTER_FOUND;
                $dw = $h->getDiscovered();
                foreach($y as $dx => $l) {
                    if($letter === $l) {
                        $dw = substr_replace($dw, $l, $dx, 1);
                    }
                }
                $h->setDiscovered($dw);
                if($dw == $h->getWord()) {
                    $returns |= Hangman::SUCCESS;
                }
            }
            $x[] = $letter;
            sort($x);
            $h->setLetters(implode('', $x));
        }
        if(!($returns & Hangman::LETTER_FOUND)) {
            $h->setTries($h->getTries() + 1);
        }
        $this->_em->persist($h);
        $this->_em->flush();
        return $returns;
    }
}
