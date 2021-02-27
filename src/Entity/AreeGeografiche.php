<?php

namespace App\Entity;

use App\Repository\AreeGeograficheRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AreeGeograficheRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class AreeGeografiche
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60)
     * @Assert\Length( max=60  )
     */
    private $area;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=Personale::class, mappedBy="areaGeografica")
     */
    private $persone;

    public function __toString(): string
    {
            return (string) $this->getArea();
    }

    public function __construct()
    {
        $this->persone = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function setArea(string $area): self
    {
        $this->area = $area;

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
     * @return Collection|Personale[]
     */
    public function getPersone(): Collection
    {
        return $this->persone;
    }

    public function addPersone(Personale $persone): self
    {
        if (!$this->persone->contains($persone)) {
            $this->persone[] = $persone;
            $persone->setAreaGeografica($this);
        }

        return $this;
    }

    public function removePersone(Personale $persone): self
    {
        if ($this->persone->removeElement($persone)) {
            // set the owning side to null (unless already changed)
            if ($persone->getAreaGeografica() === $this) {
                $persone->setAreaGeografica(null);
            }
        }

        return $this;
    }
}
