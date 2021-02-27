<?php

namespace App\Entity;

use App\Repository\MansioniRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MansioniRepository::class)
 * @ORM\HasLifecycleCallbacks() 
 */
class Mansioni
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     * @Assert\Length( max=60  ) 
     */
    private $mansione;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isValidDA;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Personale::class, inversedBy="mansione")
     */
    private $persone;


    public function __toString(): string
    {
            return (string) $this->getMansione();
    } 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMansione(): ?string
    {
        return $this->mansione;
    }

    public function setMansione(string $mansione): self
    {
        $this->mansione = $mansione;

        return $this;
    }

    public function getIsValidDA(): ?bool
    {
        return $this->isValidDA;
    }

    public function setIsValidDA(bool $isValidDA): self
    {
        $this->isValidDA = $isValidDA;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
    *    @ORM\PrePersist
    *    @ORM\PreUpdate
    */
    public function setCreatedAtValue()
    {
         $this->createdAt = new \DateTime();
    }


    public function getPersone(): ?Personale
    {
        return $this->persone;
    }

    public function setPersone(?Personale $persone): self
    {
        $this->persone = $persone;

        return $this;
    }
}
