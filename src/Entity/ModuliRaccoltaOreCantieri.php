<?php

namespace App\Entity;

use App\Repository\ModuliRaccoltaOreCantieriRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ModuliRaccoltaOreCantieriRepository::class)
 * @Assert\Callback({"App\Validator\ModuliRaccoltaOreCantieriValidator", "validate"})  
 * @ORM\HasLifecycleCallbacks()
 */
class ModuliRaccoltaOreCantieri
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     */
    private $oreGiornaliere = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $isOreConfermate = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Cantieri::class)
     */
    private $cantiere;

    /** 
     * @ORM\Column(type="smallint", nullable=true, options={"default": 0})
     */
    private $prevIdPlanned;

    /**
     * @ORM\ManyToOne(targetEntity=RaccoltaOrePersone::class, inversedBy="oreMeseCantieri")
     */
    private $raccoltaOrePersona;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOreGiornaliere(): ?array
    {
        return $this->oreGiornaliere;
    }

    public function setOreGiornaliere(array $oreGiornaliere): self
    {
        $this->oreGiornaliere = $oreGiornaliere;

        return $this;
    }

    public function getIsOreConfermate(): ?array
    {
        return $this->isOreConfermate;
    }

    public function setIsOreConfermate(array $isOreConfermate): self
    {
        $this->isOreConfermate = $isOreConfermate;

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

    public function getCantiere(): ?Cantieri
    {
        return $this->cantiere;
    }

    public function setCantiere(?Cantieri $cantiere): self
    {
        $this->cantiere = $cantiere;

        return $this;
    }

    public function getRaccoltaOrePersona(): ?RaccoltaOrePersone
    {
        return $this->raccoltaOrePersona;
    }

    public function setRaccoltaOrePersona(?RaccoltaOrePersone $raccoltaOrePersona): self
    {
        $this->raccoltaOrePersona = $raccoltaOrePersona;

        return $this;
    }

    public function getPrevIdPlanned(): ?int
    {
        return $this->prevIdPlanned;
    }

    public function setPrevIdPlanned(?int $prevIdPlanned): self
    {
        $this->prevIdPlanned = $prevIdPlanned;

        return $this;
    }
}
