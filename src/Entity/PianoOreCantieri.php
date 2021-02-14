<?php

namespace App\Entity;

use App\Repository\PianoOreCantieriRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PianoOreCantieriRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("keyReference")
 * @Assert\Callback({"App\Validator\PianoOreCantieriValidator", "validate"}) 
 */
class PianoOreCantieri
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Choice({1, 2, 3, 4, 5, 6, 7}) 
     */
    private $dayOfWeek;

    /**
     * @ORM\Column(type="string", length=5)
     * @Assert\Regex(
     *     pattern="/^[0-9.]{1,4}$/",
     *     message="Indicare un numero di ore giornaliere, per 30 minuti aggiungere .5 al numero di ore (punto e non virgola) "
     * ) 
     */
    private $orePreviste;

    /**
     * @ORM\ManyToOne(targetEntity=Personale::class, inversedBy="pianoOreCantieri")
     * @ORM\JoinColumn(nullable=false)
     */
    private $persona;

    /**
     * @ORM\ManyToOne(targetEntity=Cantieri::class, inversedBy="pianoOreCantiere")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cantiere;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     */
    private $keyReference;

    public function __toString(): string
    {
            return (string) $this->getCantiere().$this->getPersona().sprintf("%1d",$this->getDayOfWeek());
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDayOfWeek(): ?int
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(int $dayOfWeek): self
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    public function getOrePreviste(): ?string
    {
        return $this->orePreviste;
    }

    public function setOrePreviste(string $orePreviste): self
    {
        $this->orePreviste = $orePreviste;

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
        $this->keyReference = sprintf("%015d-%015d-%1d", $this->getPersona()->getId(), $this->getCantiere()->getId(), $this->getDayOfWeek());

    }
}
