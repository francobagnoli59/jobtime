<?php

namespace App\Entity;

use App\Repository\AziendeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass=AziendeRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("nickName")
 * @Assert\Callback({"App\Validator\AziendeValidator", "validate"}) 
 */
class Aziende
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=80)
     * @Assert\Length( max=80  )
     */
    private $companyName;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\Length( max=30  )
     */
    private $nickName;

    /**
     * @ORM\Column(type="string", length=60)
     * @Assert\Length( max=60  )
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\Length( min=5, max=10  )
     */
    private $zipCode;

    /**
     * @ORM\Column(type="string", length=60)
     * @Assert\Length( max=60  )
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=11)
     * @Assert\Length( min=11,  max=11 )
     * @Assert\Regex(
     *     pattern="/^[0-9]{11,11}$/",
     *     message="La partita IVA deve contenere undici numeri."
     * )
     */
    private $partitaIVA;

    /**
     * @ORM\Column(type="string", length=16)
     * @Assert\Length( min=11, max=16  )
     */
    private $fiscalCode;

     /**
     * @ORM\Column(type="string", length=4, nullable=true)
     * @Assert\Length( min=4, max=4)
     * @Assert\Regex(
     *     pattern="/^[0-9]{4,4}$/",
     *     message="Sono ammessi solo numeri."
     * )  
     */
    private $codeTransferPaghe;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Province::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $provincia;

    /**
     * @ORM\OneToMany(targetEntity=Personale::class, mappedBy="azienda")
     */
    private $personale;

    /**
     * @ORM\OneToMany(targetEntity=Cantieri::class, mappedBy="azienda")
     */
    private $cantieri;

    /**
     * @ORM\OneToMany(targetEntity=MesiAziendali::class, mappedBy="azienda")
     */
    private $mesiAziendali;

    /**
     * @ORM\OneToMany(targetEntity=OreLavorate::class, mappedBy="azienda")
     */
    private $orelavorate;

   
    public function __construct()
    {
        $this->personale = new ArrayCollection();
        $this->cantieri = new ArrayCollection();
        $this->mesiAziendali = new ArrayCollection();
        $this->orelavorate = new ArrayCollection();
    }


    public function __toString(): string
    {
            return (string) $this->getNickName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getNickName(): ?string
    {
        return $this->nickName;
    }

    public function setNickName(string $nickName): self
    {
        $this->nickName = $nickName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPartitaIVA(): ?string
    {
        return $this->partitaIVA;
    }

    public function setPartitaIVA(string $partitaIVA): self
    {
        // $this->partitaIVA = sprintf("%'.011d", $partitaIVA);
        $this->partitaIVA = $partitaIVA;
        return $this;
    }

    public function getFiscalCode(): ?string
    {
        return $this->fiscalCode;
    }

    public function setFiscalCode(string $fiscalCode): self
    {
        $this->fiscalCode = $fiscalCode;

        return $this;
    }

    public function getProvincia(): ?Province
    {
        return $this->provincia;
    }

    public function setProvincia(Province $provincia): self
    {
        $this->provincia = $provincia;

        return $this;
    }

    public function getCodeTransferPaghe(): ?string
    {
        return $this->codeTransferPaghe;
    }

    public function setCodeTransferPaghe(?string $codeTransferPaghe): self
    {
        $this->codeTransferPaghe = $codeTransferPaghe;

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

    /**
     * @return Collection|Personale[]
     */
    public function getPersonale(): Collection
    {
        return $this->personale;
    }

    public function addPersonale(Personale $personale): self
    {
        if (!$this->personale->contains($personale)) {
            $this->personale[] = $personale;
            $personale->setAzienda($this);
        }

        return $this;
    }

    public function removePersonale(Personale $personale): self
    {
        if ($this->personale->removeElement($personale)) {
            // set the owning side to null (unless already changed)
            if ($personale->getAzienda() === $this) {
                $personale->setAzienda(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Cantieri[]
     */
    public function getCantieri(): Collection
    {
        return $this->cantieri;
    }

    public function addCantieri(Cantieri $cantieri): self
    {
        if (!$this->cantieri->contains($cantieri)) {
            $this->cantieri[] = $cantieri;
            $cantieri->setAzienda($this);
        }

        return $this;
    }

    public function removeCantieri(Cantieri $cantieri): self
    {
        if ($this->cantieri->removeElement($cantieri)) {
            // set the owning side to null (unless already changed)
            if ($cantieri->getAzienda() === $this) {
                $cantieri->setAzienda(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MesiAziendali[]
     */
    public function getMesiAziendali(): Collection
    {
        return $this->mesiAziendali;
    }

    public function addMesiAziendali(MesiAziendali $mesiAziendali): self
    {
        if (!$this->mesiAziendali->contains($mesiAziendali)) {
            $this->mesiAziendali[] = $mesiAziendali;
            $mesiAziendali->setAzienda($this);
        }

        return $this;
    }

    public function removeMesiAziendali(MesiAziendali $mesiAziendali): self
    {
        if ($this->mesiAziendali->removeElement($mesiAziendali)) {
            // set the owning side to null (unless already changed)
            if ($mesiAziendali->getAzienda() === $this) {
                $mesiAziendali->setAzienda(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|OreLavorate[]
     */
    public function getOrelavorate(): Collection
    {
        return $this->orelavorate;
    }

    public function addOrelavorate(OreLavorate $orelavorate): self
    {
        if (!$this->orelavorate->contains($orelavorate)) {
            $this->orelavorate[] = $orelavorate;
            $orelavorate->setAzienda($this);
        }

        return $this;
    }

    public function removeOrelavorate(OreLavorate $orelavorate): self
    {
        if ($this->orelavorate->removeElement($orelavorate)) {
            // set the owning side to null (unless already changed)
            if ($orelavorate->getAzienda() === $this) {
                $orelavorate->setAzienda(null);
            }
        }

        return $this;
    }

   
}
