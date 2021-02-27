<?php

namespace App\Entity;

use App\Repository\ClientiRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ClientiRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("name") 
 * @Assert\Callback({"App\Validator\ClientiValidator", "validate"})  
 */
class Clienti
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=80)
     * @Assert\Length( max=80 )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\Length( max=30  )
     */
    private $nickName;

    /**
     * @ORM\Column(type="string", length=60, options={"default": " "})
     * @Assert\Length( max=60  )
     */
    private $address;

    /**
    * @ORM\Column(type="string", length=10, options={"default": "00000"})
    * @Assert\Length( min=5, max=10  )
    */
    private $zipCode;

    /**
     * @ORM\Column(type="string", length=60, options={"default": " "})
     * @Assert\Length( max=60  )
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=2, options={"default": "IT"})
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     * @Assert\Length( max = 11  )
     * @Assert\Regex(
     *     pattern="/^[0-9]*$/",
     *     message="Caratteri non validi nella partita Iva"
     * )
     */
    private $partitaIva;

    /**
     * @ORM\Column(type="string", length=16)
     * @Assert\Length( max = 16  )
     * @Assert\Regex(
     *     pattern="/^[0-9A-Z]*$/",
     *     message="Caratteri non validi nel codice fiscale"
     * )
     */
    private $fiscalCode;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $typeCliente;

    /**
     * @ORM\Column(type="string", length=7, nullable=true)
     * @Assert\Length( max = 7  )
     * @Assert\Regex(
     *     pattern="/^[0-9A-Z]*$/",
     *     message="Caratteri non validi nel codice SDI"
     * )
     */
    private $codeSdi;

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
     * @ORM\OneToMany(targetEntity=Cantieri::class, mappedBy="cliente")
     */
    private $cantieri;

    public function __construct()
    {
        $this->cantieri = new ArrayCollection();
    }

    public function getNameResult(): ?string
    {
        $nameresult = $this->getNickName();
        if ($nameresult == null || strlen($nameresult) == 0 )  {
            $nameresult = $this->getName();
         }
        return (string) substr($nameresult, 0, 30);
    }
    public function __toString(): string
    {
        return (string) $this->getNameResult();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = strtoupper($name);

        return $this;
    }

    public function getNickName(): ?string
    {
        return $this->nickName;
    }

    public function setNickName(?string $nickName): self
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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getPartitaIva(): ?string
    {
        return $this->partitaIva;
    }

    public function setPartitaIva(string $partitaIva): self
    {
        $this->partitaIva = $partitaIva;

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

    public function getTypeCliente(): ?string
    {
        return $this->typeCliente;
    }

    public function setTypeCliente(string $typeCliente): self
    {
        $this->typeCliente = $typeCliente;

        return $this;
    }

    public function getCodeSdi(): ?string
    {
        return $this->codeSdi;
    }

    public function setCodeSdi(string $codeSdi): self
    {
        $this->codeSdi = $codeSdi;

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
            $cantieri->setCliente($this);
        }

        return $this;
    }

    public function removeCantieri(Cantieri $cantieri): self
    {
        if ($this->cantieri->removeElement($cantieri)) {
            // set the owning side to null (unless already changed)
            if ($cantieri->getCliente() === $this) {
                $cantieri->setCliente(null);
            }
        }

        return $this;
    }


}
