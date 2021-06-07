<?php

namespace App\Entity;

use App\Repository\DocumentiPersonaleRepository;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass=DocumentiPersonaleRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable()
 * @Assert\Callback({"App\Validator\DocumentiPersonaleValidator", "validate"})  
 */
class DocumentiPersonale
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=3, nullable=true, options={"default": "NUL"})
     * @Assert\Choice({"NUL", "SAP", "INP", "INF", "PSG", "CID", "PAS", "PAT", "OTH"})
     */
    private $tipologia;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $scadenza;

    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     * @Assert\Length( max=80  )
     */
    private $titolo;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length( max=255  )
     */
    private $documentoPath;

    /**
     * @Vich\UploadableField(mapping="personale_documenti", fileNameProperty="documentoPath")
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
     * @ORM\ManyToOne(targetEntity=Personale::class, inversedBy="documentiPersonale")
     */
    private $persona;

   


    public function __toString(): string
    {
        $titolodoc = 'documento generico';
        $tipo = $this->getTipologia();
        if ($this->titolo === null ) {
            switch ( $tipo) {
                case "SAP":
                    $titolodoc = 'Scheda anagrafica personale ' ;
                    break;
                case "INP":
                    $titolodoc ='Invalidità Psichica ';
                    break;
                case "INF":
                    $titolodoc = 'Invalidità Fisica';
                    break;
                case "PSG":
                    $titolodoc = 'Permesso di soggiorno';
                    break;
                case "CID":
                    $titolodoc = 'Carta di Identità';
                    break;
                case "PAT":
                    $titolodoc = 'Patente auto';
                    break;
                case "PAS":
                    $titolodoc = 'Passaporto';
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

    public function getDocumentoPath(): ?string
    {
        return $this->documentoPath;
    }

    public function setDocumentoPath(?string $documentoPath): self
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

    public function getPersona(): ?Personale
    {
        return $this->persona;
    }

    public function setPersona(?Personale $persona): self
    {
        $this->persona = $persona;

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

    public function getTipologia(): ?string
    {
        return $this->tipologia;
    }

    public function setTipologia(?string $tipologia): self
    {
        $this->tipologia = $tipologia;

        return $this;
    }

    public function getScadenza(): ?\DateTimeInterface
    {
        return $this->scadenza;
    }

    public function setScadenza(?\DateTimeInterface $scadenza): self
    {
        $this->scadenza = $scadenza;

        return $this;
    }

   

}
