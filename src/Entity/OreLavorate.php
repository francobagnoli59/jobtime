<?php

namespace App\Entity;

use App\Repository\OreLavorateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=OreLavorateRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("keyReference")
 * @Assert\Callback({"App\Validator\OreLavorateValidator", "validate"}) 
 */
class OreLavorate
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $giorno;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    private $orePianificate;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     * @Assert\Regex(
     *     pattern="/^[0-9.]{1,4}$/",
     *     message="Indicare un numero di ore giornaliere, per la frazione di ora aggiungere .5 al numero di ore "
     * ) 
     */
    private $oreRegistrate;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isConfirmed;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $keyReference;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Aziende::class, inversedBy="orelavorate")
     * @ORM\JoinColumn(nullable=false)
     */
    private $azienda;

    /**
     * @ORM\ManyToOne(targetEntity=Cantieri::class, inversedBy="orelavorate")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cantiere;

    /**
     * @ORM\ManyToOne(targetEntity=Personale::class, inversedBy="orelavorate")
     * @ORM\JoinColumn(nullable=false)
     */
    private $persona;

    /**
     * @ORM\ManyToOne(targetEntity=Causali::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $causale;

    public function __toString(): string
    {
            return (string) $this->getCantiere().$this->getPersona().$this->getGiorno()->format('Y-m-d').$this->getCausale();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGiorno(): ?\DateTimeInterface
    {
        return $this->giorno;
    }

    public function setGiorno(\DateTimeInterface $giorno): self
    {
        $this->giorno = $giorno;

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

    public function getOreRegistrate(): ?string
    {
        return $this->oreRegistrate;
    }

    public function setOreRegistrate(?string $oreRegistrate): self
    {
        $this->oreRegistrate = $oreRegistrate;

        return $this;
    }

    public function getIsConfirmed(): ?bool
    {
        return $this->isConfirmed;
    }

    public function setIsConfirmed(bool $isConfirmed): self
    {
        $this->isConfirmed = $isConfirmed;

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
        $this->keyReference = sprintf("%015d-%015d-%s-%s", $this->getCantiere()->getId(), $this->getPersona()->getId(), $this->getGiorno()->format('Y-m-d'), $this->getCausale()->getCode() );

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


    public function getAzienda(): ?Aziende
    {
        return $this->azienda;
    }

    public function setAzienda(?Aziende $azienda): self
    {
        $this->azienda = $azienda;

        return $this;
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

    public function getPersona(): ?Personale
    {
        return $this->persona;
    }

    public function setPersona(?Personale $persona): self
    {
        $this->persona = $persona;

        return $this;
    }

    public function getCausale(): ?Causali
    {
        return $this->causale;
    }

    public function setCausale(?Causali $causale): self
    {
        $this->causale = $causale;

        return $this;
    }
}
