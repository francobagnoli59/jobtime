<?php

namespace App\Entity;

use App\Repository\CantieriRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
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
     * @ORM\Column(type="smallint")
     * @MasotechAssert\SmallIntRequirements()
     */
    private $distance;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     * @MasotechAssert\Decimal7_2Requirements()
     */
    private $hourlyRate;

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

   
    public function __construct()
    {
        $this->commentiPubblici = new ArrayCollection();
        $this->personale = new ArrayCollection();

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

}
