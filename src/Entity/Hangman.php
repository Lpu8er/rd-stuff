<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Hangman
 *
 * @author lpu8er
 * @ORM\Entity(repositoryClass="App\Repository\HangmanRepository")
 * @ORM\Table("hangmans")
 */
class Hangman {
    const LETTER_FOUND = 1;
    const LETTER_ALREADY_TRIED = 2;
    const NOWDED = 32;
    const SUCCESS = 64;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * 
     * @ORM\Column(type="string", length=60)
     */
    private $word;
    
    /**
     * 
     * @ORM\Column(type="string", length=26)
     */
    private $letters;
    
    /**
     * 
     * @ORM\Column(type="string", length=60)
     */
    private $discovered;
    
    /**
     * 
     * @ORM\Column(type="integer")
     */
    private $tries = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWord(): ?string
    {
        return $this->word;
    }

    public function setWord(string $word): self
    {
        $this->word = $word;

        return $this;
    }

    public function getLetters(): ?string
    {
        return $this->letters;
    }

    public function setLetters(string $letters): self
    {
        $this->letters = $letters;

        return $this;
    }

    public function getDiscovered(): ?string
    {
        return $this->discovered;
    }

    public function setDiscovered(string $discovered): self
    {
        $this->discovered = $discovered;

        return $this;
    }

    public function getTries(): ?int
    {
        return $this->tries;
    }

    public function setTries(int $tries): self
    {
        $this->tries = $tries;

        return $this;
    }
    
    public function getFunDiscovered(): string {
        $returns = '';
        $w = str_split($this->getDiscovered());
        foreach($w as $d) {
            if('-' == $d) {
                $returns .= ' :stop_button: ';
            } elseif(' ' == $d) {
                $returns .= ' :white_large_square: ';
            } else {
                $returns .= ' :regional_indicator_'.strtolower($d).': ';
            }
        }
        return $returns;
    }
    
    public function getFunWholeWord(): string {
        $returns = '';
        $w = str_split($this->getWord());
        foreach($w as $d) {
            if(' ' == $d) {
                $returns .= ' :white_large_square: ';
            } else {
                $returns .= ' :regional_indicator_'.strtolower($d).': ';
            }
        }
        return $returns;
    }
}
