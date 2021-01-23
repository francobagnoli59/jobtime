<?php

namespace App\Entity;

use App\Repository\RegoleFatturazioneRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=RegoleFatturazioneRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("billingCadence")
*/
class RegoleFatturazione
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     */
    private $billingCadence;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\GreaterThanOrEqual(value = 0)
     * @Assert\LessThanOrEqual(value = 360)
     */
    private $daysRange;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    public function __toString(): string
    {
            return (string) $this->getBillingCadence();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBillingCadence(): ?string
    {
        return $this->billingCadence;
    }

    public function setBillingCadence(string $billingCadence): self
    {
        $this->billingCadence = strtoupper($billingCadence);

        return $this;
    }

    public function getDaysRange(): ?int
    {
        return $this->daysRange;
    }

    public function setDaysRange(int $daysRange): self
    {
        $this->daysRange = $daysRange;

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

}
