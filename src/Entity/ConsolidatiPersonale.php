<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\ConsolidatiPersonaleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConsolidatiPersonaleRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("keyReference")
 */

class ConsolidatiPersonale
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     */
    private $keyReference;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=2, nullable=true)
     */
    private $oreLavoro;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=2, nullable=true)
     */
    private $oreStraordinario;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=2, nullable=true)
     */
    private $oreImproduttive;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=2, nullable=true)
     */
    private $oreIninfluenti;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=2, nullable=true)
     */
    private $orePianificate;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2, nullable=true)
     */
    private $costoLavoro;

     /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Personale::class, inversedBy="consolidatiPersonale")
     * @ORM\JoinColumn(nullable=false)
     */
    private $persona;

    /**
     * @ORM\ManyToOne(targetEntity=MesiAziendali::class, inversedBy="consolidatiPersonale")
     * @ORM\JoinColumn(nullable=false)
     */
    private $meseAziendale;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOreLavoro(): ?string
    {
        return $this->oreLavoro;
    }

    public function setOreLavoro(?string $oreLavoro): self
    {
        $this->oreLavoro = $oreLavoro;

        return $this;
    }

    public function getOreStraordinario(): ?string
    {
        return $this->oreStraordinario;
    }

    public function setOreStraordinario(?string $oreStraordinario): self
    {
        $this->oreStraordinario = $oreStraordinario;

        return $this;
    }

    public function getOreImproduttive(): ?string
    {
        return $this->oreImproduttive;
    }

    public function setOreImproduttive(?string $oreImproduttive): self
    {
        $this->oreImproduttive = $oreImproduttive;

        return $this;
    }

    public function getOreIninfluenti(): ?string
    {
        return $this->oreIninfluenti;
    }

    public function setOreIninfluenti(?string $oreIninfluenti): self
    {
        $this->oreIninfluenti = $oreIninfluenti;

        return $this;
    }

    public function getOrePianificate(): ?string
    {
        return $this->orePianificate;
    }

    public function setOrePianificate(?string $orePianificate): self
    {
        $this->orePianificate = $orePianificate;

        return $this;
    }

    public function getCostoLavoro(): ?string
    {
        return $this->costoLavoro;
    }

    public function setCostoLavoro(?string $costoLavoro): self
    {
        $this->costoLavoro = $costoLavoro;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
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
        $this->keyReference = sprintf("%010d-%010d", $this->getPersona()->getId(), $this->getMeseAziendale()->getId());

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

    public function getMeseAziendale(): ?MesiAziendali
    {
        return $this->meseAziendale;
    }

    public function setMeseAziendale(?MesiAziendali $meseAziendale): self
    {
        $this->meseAziendale = $meseAziendale;

        return $this;
    }
}
