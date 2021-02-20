<?php

namespace App\Entity;

use App\Repository\ImportPersonaleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ImportPersonaleRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Assert\Callback({"App\Validator\ImportPersonaleValidator", "validate"})  
 */
class ImportPersonale
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nota;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pathImport;

    /**
     *  @var File
     *  @Assert\File( 
     *     maxSize="1024k", 
     *     mimeTypes = {"application/xlsx"},
     *     mimeTypesMessage = "Per favore carica un file Excel (xlsx) com la dimensione massima di 1MB"
     *  )
     *  
     */
    private $excelFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Aziende::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $azienda;

    public function __toString(): string
    {
            return (string) $this->getNota();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNota(): ?string
    {
        return $this->nota;
    }

    public function setNota(?string $nota): self
    {
        $this->nota = $nota;

        return $this;
    }

    public function getPathImport(): ?string
    {
        return $this->pathImport;
    }

    public function setPathImport(string $pathImport): self
    {
        $this->pathImport = $pathImport;

        return $this;
    }

    public function setExcelFile(File $pathImport = null)
    {
        $this->excelFile = $pathImport;
    }

    public function getExcelFile()
    {
        return $this->excelFile;
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


    public function getAzienda(): ?Aziende
    {
        return $this->azienda;
    }

    public function setAzienda(?Aziende $azienda): self
    {
        $this->azienda = $azienda;

        return $this;
    }
}
