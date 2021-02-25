<?php

namespace App\Entity;

use App\Repository\CategorieServiziRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CategorieServiziRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("categoria")  
 */
class CategorieServizi
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\Length(
     *      max = 50,
     *  )  
     */
    private $categoria;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=Cantieri::class, mappedBy="categoria")
     */
    private $cantieri;

    public function __construct()
    {
        $this->cantieri = new ArrayCollection();
    }

    public function __toString(): string
    {
            return (string) $this->getCategoria();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    public function setCategoria(string $categoria): self
    {
        $this->categoria = $categoria;

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
            $cantieri->setCategoria($this);
        }

        return $this;
    }

    public function removeCantieri(Cantieri $cantieri): self
    {
        if ($this->cantieri->removeElement($cantieri)) {
            // set the owning side to null (unless already changed)
            if ($cantieri->getCategoria() === $this) {
                $cantieri->setCategoria(null);
            }
        }

        return $this;
    }
}
