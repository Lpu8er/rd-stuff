<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of DiscordUser
 *
 * @author lpu8er
 * @ORM\Entity()
 * @ORM\Table("discordusers")
 */
class DiscordUser {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     *
     * @var string 
     * @ORM\Column(type="string", length=200)
     */
    private $discordName;
    
    /**
     *
     * @var int 
     * @ORM\Column(type="integer")
     */
    private $discriminator;
    
    /**
     *
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateAdd = null;
    
    /**
     *
     * @var float
     * @ORM\Column(type="decimal")
     */
    private $money = 0.0;
    
    /**
     *
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dailyAsk = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    public function getDiscordName(): ?string
    {
        return $this->discordName;
    }

    public function setDiscordName(string $discordName): self
    {
        $this->discordName = $discordName;

        return $this;
    }

    public function getDiscriminator(): ?int
    {
        return $this->discriminator;
    }

    public function setDiscriminator(int $discriminator): self
    {
        $this->discriminator = $discriminator;

        return $this;
    }

    public function getDateAdd(): ?\DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(?\DateTimeInterface $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function getMoney()
    {
        return $this->money;
    }

    public function setMoney($money): self
    {
        $this->money = $money;

        return $this;
    }

    public function getDailyAsk(): ?\DateTimeInterface
    {
        return $this->dailyAsk;
    }

    public function setDailyAsk(?\DateTimeInterface $dailyAsk): self
    {
        $this->dailyAsk = $dailyAsk;

        return $this;
    }
}
