<?php

namespace App\Entity;

use App\Repository\CantieriRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Validator\Constraints as MasotechAssert;

/**
 * @ORM\Entity(repositoryClass=CantieriRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("nameJob")
 * @Assert\Callback({"App\Validator\CantieriValidator", "validate"}) 
 */
class Cantieri
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $nameJob;

    /**
     * @ORM\Column(type="string", length=60)
     * @Assert\Length(
     *      min = 4,
     *      max = 60,
     *  )
     */
    private $city;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublic;

    /**
     * @ORM\Column(type="date")
     */
    private $dateStartJob;

    /**
     * @ORM\Column(type="date")
     * @Assert\GreaterThanOrEqual(propertyPath="dateStartJob")
     */
    private $dateEndJob;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descriptionJob;

     /**
     * @ORM\Column(type="smallint", nullable=true)
     * @MasotechAssert\SmallIntRequirements()
     */
    private $distance;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     * @MasotechAssert\Decimal7_2Requirements()
     */
    private $hourlyRate;

    /**
    * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true, options={"default": 0})
    * @MasotechAssert\Decimal7_2Requirements()
     */
    private $extraRate;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @MasotechAssert\Decimal10_2Requirements()
     */
    private $flatRate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPlanningPerson;

    /**
     * @ORM\Column(type="smallint")
     * @MasotechAssert\SmallIntRequirements()
     */
    private $planningHours;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPlanningMaterial;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @MasotechAssert\Decimal10_2Requirements()
     */
    private $planningCostMaterial;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $mapsGoogle;

    /**
     * @ORM\Column(type="string", length=1, nullable=true, options={"default": "N"})
     * @Assert\Choice({"N", "C", "E", "O"})
     */
    private $typeOrderPA;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\Length(
     *      max = 20,
     *  )
     */
    private $numDocumento;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateDocumento;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\Length(
     *      max = 15,
     * )
     */
    private $codiceCIG;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Assert\Length(
     *      max = 15,
     * )
     */
    private $codiceCUP;

    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     * @Assert\Length( max = 6  )
     * @Assert\Regex(
     *     pattern="/^[0-9A-Z]*$/",
     *     message="Caratteri non validi nel codice univoco Ufficio P.A. "
     * )
     */
    private $codiceIPA;

    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=CommentiPubblici::class, mappedBy="cantieri", orphanRemoval=true)
     */
    private $commentiPubblici;

    /**
     * @ORM\ManyToOne(targetEntity=Province::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $provincia;

    /**
     * @ORM\ManyToOne(targetEntity=RegoleFatturazione::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $regolaFatturazione;

    /**
     * @ORM\OneToMany(targetEntity=Personale::class, mappedBy="cantiere")
     */
    private $personale;

    /**
     * @ORM\ManyToOne(targetEntity=Aziende::class, inversedBy="cantieri")
     * @ORM\JoinColumn(nullable=false)
     */
    private $azienda;

    /**
     * @ORM\ManyToOne(targetEntity=Clienti::class, inversedBy="cantieri")
     */
    private $cliente;

    /**
     * @ORM\OneToMany(targetEntity=OreLavorate::class, mappedBy="cantiere")
     */
    private $orelavorate;

    /**
     * @ORM\OneToMany(targetEntity=PianoOreCantieri::class, mappedBy="cantiere")
     */
    private $pianoOreCantiere;

    /**
     * @ORM\OneToMany(targetEntity=ConsolidatiCantieri::class, mappedBy="cantiere")
     */
    private $consolidatiCantieri;

    /**
     * @ORM\ManyToOne(targetEntity=CategorieServizi::class, inversedBy="cantieri")
     */
    private $categoria;

     /**
     * @ORM\OneToMany(targetEntity=DocumentiCantieri::class, mappedBy="cantiere", cascade={"persist"})
     */
    private $documentiCantieri;

      
    public function __construct()
    {
        $this->documentiCantieri = new ArrayCollection();
        $this->commentiPubblici = new ArrayCollection();
        $this->personale = new ArrayCollection();
        $this->orelavorate = new ArrayCollection();
        $this->pianoOreCantiere = new ArrayCollection();
        $this->consolidatiCantieri = new ArrayCollection();
        
    }

    public function __toString(): string
    {
            return $this->nameJob.' - '.$this->city;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameJob(): ?string
    {
        return $this->nameJob;
    }

    public function setNameJob(string $nameJob): self
    {
        $this->nameJob = $nameJob;

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

    
    public function getIsPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    public function getIsNotPA($typeOrder): ?bool
    {
        $isNotPA = false;
      //  $isNotPA = 'N' === $this->getTypeOrderPA();
        if ($typeOrder === 'N') {
            $isNotPA = true;
        } 
        return (bool) $isNotPA;
    }

    public function getDateStartJob(): ?\DateTimeInterface
    {
        return $this->dateStartJob;
    }

    public function setDateStartJob(\DateTimeInterface $dateStartJob): self
    {
        $this->dateStartJob = $dateStartJob;

        return $this;
    }

    public function getDateEndJob(): ?\DateTimeInterface
    {
        return $this->dateEndJob;
    }

    public function setDateEndJob(\DateTimeInterface $dateEndJob): self
    {
        $this->dateEndJob = $dateEndJob;

        return $this;
    }

    public function getDescriptionJob(): ?string
    {
        return $this->descriptionJob;
    }

    public function setDescriptionJob(?string $descriptionJob): self
    {
        $this->descriptionJob = $descriptionJob;

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getHourlyRate(): ?string
    {
        return $this->hourlyRate;
    }

    public function setHourlyRate(string $hourlyRate): self
    {
        $this->hourlyRate = $hourlyRate;

        return $this;
    }

    public function getFlatRate(): ?string
    {
        return $this->flatRate;
    }

    public function setFlatRate(string $flatRate): self
    {
        $this->flatRate = $flatRate;

        return $this;
    }

   
    public function getIsPlanningPerson(): ?bool
    {
        return $this->isPlanningPerson;
    }

    public function setIsPlanningPerson(bool $isPlanningPerson): self
    {
        $this->isPlanningPerson = $isPlanningPerson;

        return $this;
    }

    public function getPlanningHours(): ?int
    {
        return $this->planningHours;
    }

    public function setPlanningHours(int $planningHours): self
    {
        $this->planningHours = $planningHours;

        return $this;
    }

    public function getIsPlanningMaterial(): ?bool
    {
        return $this->isPlanningMaterial;
    }

    public function setIsPlanningMaterial(bool $isPlanningMaterial): self
    {
        $this->isPlanningMaterial = $isPlanningMaterial;

        return $this;
    }

    public function getPlanningCostMaterial(): ?string
    {
        return $this->planningCostMaterial;
    }

    public function setPlanningCostMaterial(string $planningCostMaterial): self
    {
        $this->planningCostMaterial = $planningCostMaterial;

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
     * @return Collection|CommentiPubblici[]
     */
    public function getCommentiPubblici(): Collection
    {
        return $this->commentiPubblici;
    }

    public function addCommentiPubblici(CommentiPubblici $commentiPubblici): self
    {
        if (!$this->commentiPubblici->contains($commentiPubblici)) {
            $this->commentiPubblici[] = $commentiPubblici;
            $commentiPubblici->setCantieri($this);
        }

        return $this;
    }

    public function removeCommentiPubblici(CommentiPubblici $commentiPubblici): self
    {
        if ($this->commentiPubblici->removeElement($commentiPubblici)) {
            // set the owning side to null (unless already changed)
            if ($commentiPubblici->getCantieri() === $this) {
                $commentiPubblici->setCantieri(null);
            }
        }

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

    public function getRegolaFatturazione(): ?RegoleFatturazione
    {
        return $this->regolaFatturazione;
    }

    public function setRegolaFatturazione(RegoleFatturazione $regolaFatturazione): self
    {
        $this->regolaFatturazione = $regolaFatturazione;

        return $this;
    }

    public function getMapsGoogle(): ?string
    {
        return $this->mapsGoogle;
    }

    public function setMapsGoogle(?string $mapsGoogle): self
    {
        $this->mapsGoogle = $mapsGoogle;

        return $this;
    }

    public function getNumDocumento(): ?string
    {
        return $this->numDocumento;
    }

    public function setNumDocumento(?string $numDocumento): self
    {
        $this->numDocumento = $numDocumento;

        return $this;
    }

    public function getDateDocumento(): ?\DateTimeInterface
    {
        return $this->dateDocumento;
    }

    public function setDateDocumento(?\DateTimeInterface $dateDocumento): self
    {
        $this->dateDocumento = $dateDocumento;

        return $this;
    }

    public function getCodiceCIG(): ?string
    {
        return $this->codiceCIG;
    }

    public function setCodiceCIG(?string $codiceCIG): self
    {
        $this->codiceCIG = $codiceCIG;

        return $this;
    }

    public function getCodiceCUP(): ?string
    {
        return $this->codiceCUP;
    }

    public function setCodiceCUP(?string $codiceCUP): self
    {
        $this->codiceCUP = $codiceCUP;

        return $this;
    }

    public function getcodiceIPA(): ?string
    {
        return $this->codiceIPA;
    }

    public function setcodiceIPA(string $codiceIPA): self
    {
        $this->codiceIPA = $codiceIPA;

        return $this;
    }

    public function getTypeOrderPA(): ?string
    {
        return $this->typeOrderPA;
    }

    public function setTypeOrderPA(?string $typeOrderPA): self
    {
        $this->typeOrderPA = $typeOrderPA;

        return $this;
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
            $personale->setCantiere($this);
        }

        return $this;
    }

    public function removePersonale(Personale $personale): self
    {
        if ($this->personale->removeElement($personale)) {
            // set the owning side to null (unless already changed)
            if ($personale->getCantiere() === $this) {
                $personale->setCantiere(null);
            }
        }

        return $this;
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

    public function getCliente(): ?Clienti
    {
        return $this->cliente;
    }

    public function setCliente(?Clienti $cliente): self
    {
        $this->cliente = $cliente;

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
            $orelavorate->setCantiere($this);
        }

        return $this;
    }

    public function removeOrelavorate(OreLavorate $orelavorate): self
    {
        if ($this->orelavorate->removeElement($orelavorate)) {
            // set the owning side to null (unless already changed)
            if ($orelavorate->getCantiere() === $this) {
                $orelavorate->setCantiere(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PianoOreCantieri[]
     */
    public function getPianoOreCantiere(): Collection
    {
        return $this->pianoOreCantiere;
    }

    public function addPianoOreCantiere(PianoOreCantieri $pianoOreCantiere): self
    {
        if (!$this->pianoOreCantiere->contains($pianoOreCantiere)) {
            $this->pianoOreCantiere[] = $pianoOreCantiere;
            $pianoOreCantiere->setCantiere($this);
        }

        return $this;
    }

    public function removePianoOreCantiere(PianoOreCantieri $pianoOreCantiere): self
    {
        if ($this->pianoOreCantiere->removeElement($pianoOreCantiere)) {
            // set the owning side to null (unless already changed)
            if ($pianoOreCantiere->getCantiere() === $this) {
                $pianoOreCantiere->setCantiere(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ConsolidatiCantieri[]
     */
    public function getConsolidatiCantieri(): Collection
    {
        return $this->consolidatiCantieri;
    }

    public function addConsolidatiCantieri(ConsolidatiCantieri $consolidatiCantieri): self
    {
        if (!$this->consolidatiCantieri->contains($consolidatiCantieri)) {
            $this->consolidatiCantieri[] = $consolidatiCantieri;
            $consolidatiCantieri->setCantiere($this);
        }

        return $this;
    }

    public function removeConsolidatiCantieri(ConsolidatiCantieri $consolidatiCantieri): self
    {
        if ($this->consolidatiCantieri->removeElement($consolidatiCantieri)) {
            // set the owning side to null (unless already changed)
            if ($consolidatiCantieri->getCantiere() === $this) {
                $consolidatiCantieri->setCantiere(null);
            }
        }

        return $this;
    }

    public function getExtraRate(): ?string
    {
        return $this->extraRate;
    }

    public function setExtraRate(?string $extraRate): self
    {
        $this->extraRate = $extraRate;

        return $this;
    }

    public function getCategoria(): ?CategorieServizi
    {
        return $this->categoria;
    }

    public function setCategoria(?CategorieServizi $categoria): self
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * @return Collection|DocumentiCantieri[]
     */
    public function getDocumentiCantieri(): Collection
    {
        return $this->documentiCantieri;
    }

    public function addDocumentiCantieri(DocumentiCantieri $documentiCantieri): self
    {
        if (!$this->documentiCantieri->contains($documentiCantieri)) {
            $this->documentiCantieri[] = $documentiCantieri;
            $documentiCantieri->setCantiere($this);
        }
        return $this;
    }

    public function removeDocumentiCantieri(DocumentiCantieri $documentiCantieri): self
    {
        if ($this->documentiCantieri->removeElement($documentiCantieri)) {
            // set the owning side to null (unless already changed)
            if ($documentiCantieri->getCantiere() === $this) {
                $documentiCantieri->setCantiere(null);
            }
        }
        return $this;
    }

}
