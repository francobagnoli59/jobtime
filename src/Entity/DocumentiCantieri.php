<?php

namespace App\Entity;

use App\Repository\DocumentiCantieriRepository;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=DocumentiCantieriRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable()
 */
class DocumentiCantieri
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=80)
     */
    private $titolo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $documentoPath;

    /**
     * @Vich\UploadableField(mapping="cantieri_documenti", fileNameProperty="documentoPath")
     */
    private $documentoFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Cantieri::class, inversedBy="documentiCantieri" )
     * @ORM\JoinColumn(name="cantiere_id", referencedColumnName="id", onDelete="CASCADE") 
     */
    private $cantiere;


    public function __toString(): string
    {
            return $this->titolo;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitolo(): ?string
    {
        return $this->titolo;
    }

    public function setTitolo(string $titolo): self
    {
        $this->titolo = $titolo;

        return $this;
    }

    public function getDocumentoPath(): ?string
    {
        return $this->documentoPath;
    }

    public function setDocumentoPath(string $documentoPath): self
    {
        $this->documentoPath = $documentoPath;

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

    
    public function getCantiere(): ?Cantieri
    {
        return $this->cantiere;
    }

    public function setCantiere(?Cantieri $cantiere): self
    {
        $this->cantiere = $cantiere;

        return $this;
    }

    /**
    *    @return mixed 
    */
    public function getDocumentoFile()
    {
        return $this->documentoFile;
    }

    /**
    *    @param mixed $documentoFile
    *    @throws \Exception
    */
    public function setDocumentoFile($documentoFile): void
    {
        $this->documentoFile = $documentoFile;
        if ($documentoFile) {
            $this->createdAt = new \DateTime('now');
        }
    }

   

}
