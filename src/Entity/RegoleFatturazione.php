<?php

namespace App\Entity;

use App\Repository\RegoleFatturazioneRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RegoleFatturazioneRepository::class)
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
     * @ORM\Column(type="string", length=20)
     */
    private $billingCadence;

    /**
     * @ORM\Column(type="smallint")
     */
    private $daysRange;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

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
        $this->billingCadence = $billingCadence;

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
}
