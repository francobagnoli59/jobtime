<?php

namespace App\Entity;

use App\Repository\MesiAziendaliRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MesiAziendaliRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("keyReference")
 */
class MesiAziendali
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2)
    * @Assert\Length( min=2,  max=2 )
     * @Assert\Regex(
     *     pattern="/^[0-9]{2,2}$/",
     *     message="Mensilità non valida"
     * ) 
     */
    private $mese;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2, nullable=true)
     */
    private $costMonthHuman;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2, nullable=true)
     */
    private $costMonthMaterial;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=2, nullable=true)
     */
    private $incomeMonth;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isHoursCompleted;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isInvoicesCompleted;

    /**
     * @ORM\Column(type="string", length=30, unique=true)
     */
    private $keyReference;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Aziende::class, inversedBy="mesiAziendali")
     * @ORM\JoinColumn(nullable=false)
     */
    private $azienda;

    /**
     * @ORM\ManyToOne(targetEntity=FestivitaAnnuali::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $festivitaAnnuale;


    public function __toString(): string
    {
            return (string) $this->getAzienda().$this->getFestivitaAnnuale().$this->getMese();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCostMonthHuman(): ?string
    {
        return $this->costMonthHuman;
    }

    public function setCostMonthHuman(?string $costMonthHuman): self
    {
        $this->costMonthHuman = $costMonthHuman;

        return $this;
    }

    public function getCostMonthMaterial(): ?string
    {
        return $this->costMonthMaterial;
    }

    public function setCostMonthMaterial(?string $costMonthMaterial): self
    {
        $this->costMonthMaterial = $costMonthMaterial;

        return $this;
    }

    public function getIncomeMonth(): ?string
    {
        return $this->incomeMonth;
    }

    public function setIncomeMonth(?string $incomeMonth): self
    {
        $this->incomeMonth = $incomeMonth;

        return $this;
    }

    public function getIsHoursCompleted(): ?bool
    {
        return $this->isHoursCompleted;
    }

    public function setIsHoursCompleted(bool $isHoursCompleted): self
    {
        $this->isHoursCompleted = $isHoursCompleted;

        return $this;
    }

    public function getIsInvoicesCompleted(): ?bool
    {
        return $this->isInvoicesCompleted;
    }

    public function setIsInvoicesCompleted(bool $isInvoicesCompleted): self
    {
        $this->isInvoicesCompleted = $isInvoicesCompleted;

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
        $this->keyReference = sprintf("%015d-%s-%s", $this->getAzienda()->getId(), $this->getFestivitaAnnuale(), $this->getMese());

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

    public function getFestivitaAnnuale(): ?FestivitaAnnuali
    {
        return $this->festivitaAnnuale;
    }

    public function setFestivitaAnnuale(?FestivitaAnnuali $festivitaAnnuale): self
    {
        $this->festivitaAnnuale = $festivitaAnnuale;

        return $this;
    }
}
