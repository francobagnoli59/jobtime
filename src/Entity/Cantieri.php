<?php

namespace App\Entity;

use App\Repository\CantieriRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CantieriRepository::class)
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
     * @ORM\Column(type="string", length=255)
     */
    private $nameJob;

    /**
     * @ORM\Column(type="string", length=60)
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
     */
    private $dateEndJob;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descriptionJob;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $maps;

    /**
     * @ORM\Column(type="smallint")
     */
    private $distance;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $hourlyRate;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $flatRate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPlanningPerson;

    /**
     * @ORM\Column(type="smallint")
     */
    private $planningHours;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPlanningMaterial;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $planningCostMaterial;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=CommentiPubblici::class, mappedBy="cantieri", orphanRemoval=true)
     */
    private $commentiPubblici;

    /**
     * @ORM\OneToOne(targetEntity=Province::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $provincia;

    /**
     * @ORM\OneToOne(targetEntity=RegoleFatturazione::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $regolaFatturazione;

    public function __construct()
    {
        $this->commentiPubblici = new ArrayCollection();
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

    public function getMaps(): ?string
    {
        return $this->maps;
    }

    public function setMaps(?string $maps): self
    {
        $this->maps = $maps;

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

}
