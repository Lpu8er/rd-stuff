<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Word
 *
 * @author lpu8er
 * @ORM\Entity()
 * @ORM\Table("words")
 */
class Word {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="string", length=60)
     */
    private $word;

    public function getWord(): ?string
    {
        return $this->word;
    }
    
    public function setWord(string $word): self {
        $this->word = $word;
        return $this;
    }
}
