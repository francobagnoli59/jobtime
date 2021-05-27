<?php

namespace App\Entity;

use App\Repository\MovimentiAttrezzatureRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MovimentiAttrezzatureRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class MovimentiAttrezzature
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $dataMovimento;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    
    /**
     * @ORM\ManyToOne(targetEntity=Attrezzature::class, inversedBy="movimentiAttrezzature")
     * @ORM\JoinColumn(nullable=false)
     */
    private $attrezzatura;

    /**
     * @ORM\ManyToOne(targetEntity=Cantieri::class, inversedBy="movimentiAttrezzature")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cantiere;

    /**
     * @ORM\ManyToOne(targetEntity=Personale::class, inversedBy="movimentiAttrezzature")
     * @ORM\JoinColumn(nullable=false)
     */
    private $persona;

 
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDataMovimento(): ?\DateTimeInterface
    {
        return $this->dataMovimento;
    }

    public function setDataMovimento(\DateTimeInterface $dataMovimento): self
    {
        $this->dataMovimento = $dataMovimento;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

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


    public function getAttrezzatura(): ?Attrezzature
    {
        return $this->attrezzatura;
    }

    public function setAttrezzatura(?Attrezzature $attrezzatura): self
    {
        $this->attrezzatura = $attrezzatura;

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

    
    public function getPersona(): ?Personale
    {
        return $this->persona;
    }

    public function setPersona(?Personale $persona): self
    {
        $this->persona = $persona;

        return $this;
    }



}
