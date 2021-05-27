<?php

namespace App\Entity;

use App\Repository\AttrezzatureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Validator\Constraints as MasotechAssert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=AttrezzatureRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("name")
 * @Vich\Uploadable() 
 */
class Attrezzature
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\Length( max = 60  ) 
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $funzione;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photoAttrezzo;

    /**
     * @Vich\UploadableField(mapping="attrezzature_images", fileNameProperty="photoAttrezzo")
     * @var File
     */
    private $imageVFAttrezzo;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isOutOfOrder;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dataAcquisto;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $riferimentiAcquisto;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $riferimentoCespite;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $scadenzaManutenzione;

    /**
    * @ORM\Column(type="decimal", precision=9, scale=2, nullable=true, options={"default": 0})
    * @MasotechAssert\Decimal10_2Requirements()
    */
    private $costo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;
   
    /**
     * @ORM\OneToMany(targetEntity=MovimentiAttrezzature::class, mappedBy="attrezzatura")
     */
    private $movimentiAttrezzature;

    public function __construct()
    {
        $this->movimentiAttrezzature = new ArrayCollection();
    }

    public function __toString(): string
    {
            return (string) $this->getName();
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
        $this->name = $name;

        return $this;
    }

    public function getFunzione(): ?string
    {
        return $this->funzione;
    }

    public function setFunzione(?string $funzione): self
    {
        $this->funzione = $funzione;

        return $this;
    }

    public function getPhotoAttrezzo(): ?string
    {
        return $this->photoAttrezzo;
    }

    public function setPhotoAttrezzo(?string $photoAttrezzo): self
    {
        $this->photoAttrezzo = $photoAttrezzo;

        return $this;
    }

    public function setimageVFAttrezzo(File $photoAttrezzo = null)
    {
        $this->imageVFAttrezzo = $photoAttrezzo;

        // VERY IMPORTANT per VICH:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($photoAttrezzo) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->createdAt = new \DateTime('now');
        }
    }

    public function getimageVFAttrezzo()
    {
        return $this->imageVFAttrezzo;
    }

    public function getIsOutOfOrder(): ?bool
    {
        return $this->isOutOfOrder;
    }

    public function setIsOutOfOrder(bool $isOutOfOrder): self
    {
        $this->isOutOfOrder = $isOutOfOrder;
        return $this;
    }


    public function getDataAcquisto(): ?\DateTimeInterface
    {
        return $this->dataAcquisto;
    }

    public function setDataAcquisto(?\DateTimeInterface $dataAcquisto): self
    {
        $this->dataAcquisto = $dataAcquisto;

        return $this;
    }

    public function getRiferimentiAcquisto(): ?string
    {
        return $this->riferimentiAcquisto;
    }

    public function setRiferimentiAcquisto(?string $riferimentiAcquisto): self
    {
        $this->riferimentiAcquisto = $riferimentiAcquisto;

        return $this;
    }

    public function getCosto(): ?string
    {
        return $this->costo;
    }

    public function setCosto(?string $costo): self
    {
        $this->costo = $costo;

        return $this;
    }


    public function getRiferimentoCespite(): ?string
    {
        return $this->riferimentoCespite;
    }

    public function setRiferimentoCespite(?string $riferimentoCespite): self
    {
        $this->riferimentoCespite = $riferimentoCespite;

        return $this;
    }

    public function getScadenzaManutenzione(): ?\DateTimeInterface
    {
        return $this->scadenzaManutenzione;
    }

    public function setScadenzaManutenzione(?\DateTimeInterface $scadenzaManutenzione): self
    {
        $this->scadenzaManutenzione = $scadenzaManutenzione;

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
     * @return Collection|MovimentiAttrezzature[]
     */
    public function getMovimentiAttrezzature(): Collection
    {
        return $this->movimentiAttrezzature;
    }

    public function addMovimentiAttrezzature(MovimentiAttrezzature $movimentiAttrezzature): self
    {
        if (!$this->movimentiAttrezzature->contains($movimentiAttrezzature)) {
            $this->movimentiAttrezzature[] = $movimentiAttrezzature;
            $movimentiAttrezzature->setAttrezzatura($this);
        }

        return $this;
    }

    public function removeMovimentiAttrezzature(MovimentiAttrezzature $movimentiAttrezzature): self
    {
        if ($this->movimentiAttrezzature->removeElement($movimentiAttrezzature)) {
            // set the owning side to null (unless already changed)
            if ($movimentiAttrezzature->getAttrezzatura() === $this) {
                $movimentiAttrezzature->setAttrezzatura(null);
            }
        }

        return $this;
    }
   

    public function getLastLocation()
    {   
        // ultima posizione attrezzatura
        $movAttrezzo = $this->getMovimentiAttrezzature();
        $cantiereAttrezzo = 'nessuna destinazione';
        $dataSpostamento = '';
        foreach ($movAttrezzo as $ma) {
            if ($ma->getAttrezzatura() === $this) {
                    $cantiereAttrezzo = $ma->getCantiere()->getNameJob() ;
                    $dataSpostamento = $ma->getDataMovimento()->format('d-m-Y');
               }
        } 
        if ($dataSpostamento !== '') {
            $cantiereAttrezzo = $cantiereAttrezzo.' dal '.$dataSpostamento;
        }
        return $cantiereAttrezzo;
    }
}
