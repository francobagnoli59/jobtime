<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\ConsolidatiCantieriRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConsolidatiCantieriRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("keyReference")
 */
class ConsolidatiCantieri
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
     * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    private $oreLavoro;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    private $oreStraordinario;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    private $oreImproduttive;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    private $oreIninfluenti;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    private $orePianificate;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $costoOreLavoro;
     
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Cantieri::class, inversedBy="consolidatiCantieri")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cantiere;

    /**
     * @ORM\ManyToOne(targetEntity=MesiAziendali::class, inversedBy="consolidatiCantieri")
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

    public function setOrePianificate(string $orePianificate): self
    {
        $this->orePianificate = $orePianificate;

        return $this;
    }

    public function getCostoOreLavoro(): ?string
    {
        return $this->costoOreLavoro;
    }

    public function setCostoOreLavoro(?string $costoOreLavoro): self
    {
        $this->costoOreLavoro = $costoOreLavoro;

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
        $this->keyReference = sprintf("%010d-%010d", $this->getCantiere()->getId(), $this->getMeseAziendale()->getId());

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
