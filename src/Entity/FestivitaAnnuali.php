<?php

namespace App\Entity;

use App\Repository\FestivitaAnnualiRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=FestivitaAnnualiRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("anno")
 * @Assert\Callback({"App\Validator\FestivitaAnnualiValidator", "validate"})  
 */
class FestivitaAnnuali
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=4, unique=true)
     * @Assert\Length( min=4,  max=4 )
     * @Assert\Regex(
     *     pattern="/^[0-9]{4,4}$/",
     *     message="Indicare l'anno comprensivo del secolo, es. 2021, 2022.."
     * ) 
     */
    private $anno;

    /**
     * @ORM\Column(type="array")
     */
    private $dateFestivita = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    
    public function __toString(): string
    {
            return (string) $this->getAnno();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnno(): ?string
    {
        return $this->anno;
    }

    public function setAnno(string $anno): self
    {
        $this->anno = $anno;

        return $this;
    }

    public function getDateFestivita(): ?array
    {
        return $this->dateFestivita;
    }

    public function setDateFestivita(array $dateFestivita): self
    {
        $this->dateFestivita = $dateFestivita;

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

}
