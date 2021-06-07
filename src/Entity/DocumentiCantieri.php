<?php

namespace App\Entity;

use App\Repository\DocumentiCantieriRepository;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DocumentiCantieriRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable()
 * @Assert\Callback({"App\Validator\DocumentiCantieriValidator", "validate"})    
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
     * @ORM\Column(type="string", length=3, nullable=true, options={"default": "NUL"})
     * @Assert\Choice({"NUL", "OTH", "CPA", "DPA", "CPR", "OPR"})
     */
    private $tipologia;
    
    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     * @Assert\Length( max=80  ) 
     */
    private $titolo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length( max=255  ) 
     * @var string
     */
    private $documentoName;

    /**
     * @Vich\UploadableField(mapping="cantieri_documenti", fileNameProperty="documentoName")
     *  @var File
     *  @Assert\File( 
     *     maxSize="4096k", 
     *     mimeTypes = {"application/pdf", "application/x-pdf", "image/png", "image/jpeg", "image/bmp" },
     *     mimeTypesMessage = "Per favore carica un file PDF o immagini png,bmp,jpeg"
     *  )
     */
    private $documentoFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Cantieri::class, inversedBy="documentiCantiere")
     */
    private $cantiere;

   

    public function __toString(): string
    {
        $titolodoc = 'documento generico';
        $tipo = $this->getTipologia();
        if ($this->titolo === null ) {
            switch ( $tipo) {
                case "CPA":
                    $titolodoc = 'Contratto pubblica amministrazione ' ;
                    break;
                case "DPA":
                    $titolodoc = 'Determina pubblica amministrazione ';
                    break;
                case "CPR":
                    $titolodoc = 'Contratto con società non P.A.';
                    break;
                case "OPR":
                    $titolodoc = 'Commessa/Ordine con società non P.A.';
                    break;
                }
        } else { $titolodoc = $this->titolo; }

            return $titolodoc;
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

    public function getDocumentoName(): ?string
    {
        return $this->documentoName;
    }

    public function setDocumentoName(?string $documentoName): self
    {
        $this->documentoName = $documentoName;

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

    
    public function getDocumentoFile()
    {
        return $this->documentoFile;
    }

    
    public function setDocumentoFile(File $documentoName = null)
    {
        $this->documentoFile = $documentoName;
        if ($documentoName) {
            $this->createdAt = new \DateTime('now');
        }
    }

    public function getTipologia(): ?string
    {
        return $this->tipologia;
    }

    public function setTipologia(?string $tipologia): self
    {
        $this->tipologia = $tipologia;

        return $this;
    }
}
