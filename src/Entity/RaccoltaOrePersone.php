<?php

namespace App\Entity;

use App\Repository\RaccoltaOrePersoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=RaccoltaOrePersoneRepository::class)
 * @Assert\Callback({"App\Validator\RaccoltaOrePersoneValidator", "validate"})  
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("keyReference") 
 */
class RaccoltaOrePersone
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=FestivitaAnnuali::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $anno;

    /**
     * @ORM\Column(type="string", length=2)
     * @Assert\Choice({"01","02","03","04", "05", "06", "07", "08", "09", "10", "11", "12"}) 
     */
    private $mese;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=30, unique=true)
     */
    private $keyReference;

    /**
     * @ORM\ManyToOne(targetEntity=Personale::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $persona;

    /**
     * @ORM\OneToMany(targetEntity=ModuliRaccoltaOreCantieri::class, mappedBy="raccoltaOrePersona", cascade={"persist"})
     */
    private $oreMeseCantieri;


    public function __construct()
    {
        $this->oreMeseCantieri = new ArrayCollection();
    }

    public function __toString(): string
    {
            return (string) $this->getPersona().$this->getAnno().$this->getMese();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnno(): ?FestivitaAnnuali
    {
        return $this->anno;
    }

    public function setAnno(?FestivitaAnnuali $anno): self
    {
        $this->anno = $anno;

        return $this;
    }

    public function getMese(): ?string
    {
        return $this->mese;
    }

    public function setMese(string $mese): self
    {
        $this->mese = $mese;

        return $this;
    }

    public function getKeyReference(): ?string
    {
        return $this->keyReference;
    }

    public function setKeyReference(string $keyReference): self
    {
        $this->keyReference = $keyReference;

        return $this;
    } 

    /**
    *    @ORM\PrePersist
    *    @ORM\PreUpdate
    */
    public function setKeyReferenceValue()
    {
        $this->keyReference = sprintf("%010d-%s-%s", $this->getPersona()->getId(), $this->getAnno(), $this->getMese());

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

    public function getPersona(): ?Personale
    {
        return $this->persona;
    }

    public function setPersona(?Personale $persona): self
    {
        $this->persona = $persona;

        return $this;
    }

    /**
     * @return Collection|ModuliRaccoltaOreCantieri[]
     */
    public function getOreMeseCantieri(): Collection
    {
        return $this->oreMeseCantieri;
    }

    public function addOreMeseCantieri(ModuliRaccoltaOreCantieri $oreMeseCantieri): self
    {
        if (!$this->oreMeseCantieri->contains($oreMeseCantieri)) {
            $this->oreMeseCantieri[] = $oreMeseCantieri;
            $oreMeseCantieri->setRaccoltaOrePersona($this);
        }

        return $this;
    }

    public function removeOreMeseCantieri(ModuliRaccoltaOreCantieri $oreMeseCantieri): self
    {
        if ($this->oreMeseCantieri->removeElement($oreMeseCantieri)) {
            // set the owning side to null (unless already changed)
            if ($oreMeseCantieri->getRaccoltaOrePersona() === $this) {
                $oreMeseCantieri->setRaccoltaOrePersona(null);
            }
        }

        return $this;
    }
}
