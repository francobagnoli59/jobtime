<?php

namespace App\Entity;

use App\Repository\MansioniRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private $mansioneName;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isValidDA;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=Personale::class, mappedBy="mansione")
     */
    private $persone;


    public function __toString(): string
    {
            return (string) $this->mansioneName;
    } 

    public function __construct()
    {
        $this->persone = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMansioneName(): ?string
    {
        return $this->mansioneName;
    }

    public function setMansioneName(string $mansioneName): self
    {
        $this->mansioneName = $mansioneName;

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


   /**
     * @return Collection|Personale[]
     */
    public function getPersone(): Collection
    {
        return $this->persone;
    }

    public function addPersone(Personale $persone): self
    {
        if (!$this->persone->contains($persone)) {
            $this->persone[] = $persone;
            $persone->setMansione($this);
        }

        return $this;
    }

    public function removePersone(Personale $persone): self
    {
        if ($this->persone->removeElement($persone)) {
            // set the owning side to null (unless already changed)
            if ($persone->getMansione() === $this) {
                $persone->setMansione(null);
            }
        }

        return $this;
    }
}
