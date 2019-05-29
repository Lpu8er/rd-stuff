<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of HangmanUser
 *
 * @author lpu8er
 * @ORM\Entity()
 * @ORM\Table("hangmanusers")
 */
class HangmanUser {
    /**
     *
     * @var Hangman
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Hangman")
     * @ORM\JoinColumn(name="hangman_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $hangman;
    
    /**
     *
     * @var DiscordUser
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="DiscordUser")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    public function getHangman(): ?Hangman
    {
        return $this->hangman;
    }

    public function setHangman(?Hangman $hangman): self
    {
        $this->hangman = $hangman;

        return $this;
    }

    public function getUser(): ?DiscordUser
    {
        return $this->user;
    }

    public function setUser(?DiscordUser $user): self
    {
        $this->user = $user;

        return $this;
    }
}
