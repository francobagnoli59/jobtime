<?php

namespace App\Entity;

use App\Repository\PersonaleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Validator\Constraints as MasotechAssert;

/**
 * @ORM\Entity(repositoryClass=PersonaleRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Assert\Callback({"App\Validator\PersonaleValidator", "validate"}) 
 * @Vich\Uploadable
 */
class Personale
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     * @Assert\Length( max = 40  )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=40)
     * @Assert\Length( max = 40  )
     */
    private $surname;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private $gender;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthday;

    /**
     * @ORM\Column(type="string", length=60, options={"default": " "})
     * @Assert\Length( max = 60  )
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=10, options={"default": "00000"})
     * @Assert\Length( min=5, max=10  )
     */
    private $zipCode;

    /**
     * @ORM\Column(type="string", length=60, options={"default": " "})
     * @Assert\Length( max = 60  )
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=16, options={"default": " "}) 
     * @Assert\Length( max = 16  )
     * @Assert\Regex(
     *     pattern="/^[0-9A-Z]*$/",
     *     message="Caratteri non validi nel codice fiscale. "
     * )
     */
    private $fiscalCode;

    /**
     * @ORM\Column(type="string", length=6, options={"default": "000000"})
     * @Assert\Length( max = 6  )
     * @Assert\Regex(
     *     pattern="/^[0-9]*$/",
     *     message="La matricola può contenere solo numeri. "
     * )
     */
    private $matricola;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Email(
     *     message = "La email '{{ value }}' non è valida."
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\Length( max = 20  )
     *  @Assert\Regex(
     *     pattern="/^[0-9+()\s]*$/",
     *     message="Il telefono può contenere solo numeri, lo spazio e i simboli ( ) + "
     * )
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\Length( max = 20  )
     * @Assert\Regex(
     *     pattern="/^[0-9+()\s]*$/",
     *     message="Il cellulare può contenere solo numeri, lo spazio e i simboli ( ) +"
     * )
     */
    private $mobile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photoAvatar;

    /*
    * @var File
    * @Assert\File( 
      *   maxSize="1048k",  mimeTypes="image/jpeg" ) 
      
    protected $imagePhoto;
     */
           
    /**
     * @Vich\UploadableField(mapping="personale_images", fileNameProperty="photoAvatar")
     * @var File
     */
    private $imageVichFile;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $isEnforce;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateHiring;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateDismissal;

    /**
     * @ORM\Column(type="array", nullable=true )
     */
    private $planHourWeek = [0,0,0,0,0,0,0];

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2, options={"default": 0})
     * @MasotechAssert\Decimal7_2Requirements()
     */
    private $fullCostHour;

    /**
     * @ORM\Column(type="string", length=27, nullable=true)
     * @Assert\Length( max = 27  )
     * @Assert\Iban(
     *     message="Inserire un valido International Bank Account Number (IBAN)."
     * ) 
     */
    private $ibanConto;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @Assert\Length( max = 60  )
     */
    private $intestatarioConto;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $curriculumVitae;

    /**
     *  @var File
     *  @Assert\File( 
     *     maxSize="1024k", 
     *     mimeTypes = {"application/pdf", "application/x-pdf"},
     *     mimeTypesMessage = "Per favore carica un file PDF"
     *  )
     *  
     */
    private $pdfCvFile;

    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Province::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $provincia;

    /**
     * @ORM\ManyToOne(targetEntity=Cantieri::class, inversedBy="personale")
     */
    private $cantiere;

    /**
     * @ORM\ManyToOne(targetEntity=Aziende::class, inversedBy="personale")
     */
    private $azienda;


    public function getFullName(): string
    {
        return $this->getSurname().' '.$this->getName();
    }

    public function getEta(): string
    {
        $dnow = new \DateTime();
        $eta = $dnow->diff($this->getBirthday())->format("%y");
        $format = '%d anni';
        return (string) sprintf($format, $eta);
    }

    public function __toString(): string
    {
            return (string) $this->getFullName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getPlanHourWeek(): ?array
    {
        return $this->planHourWeek;
    }

    public function setPlanHourWeek(?array $planHourWeek): self
    {
        $this->planHourWeek = $planHourWeek;

        return $this;
    }

    public function getTotalHourWeek()
    {   
        
      $hourdayarray = $this->getPlanHourWeek();
        $tothour = 0;
        foreach ($hourdayarray as $d) {
            if (is_numeric($d)) {
                $tothour +=$d ;
            }
        }
        return $tothour; 
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getFiscalCode(): ?string
    {
        return $this->fiscalCode;
    }

    public function setFiscalCode(string $fiscalCode): self
    {
        $this->fiscalCode = strtoupper($fiscalCode);

        return $this;
    }

    public function getMatricola(): ?string
    {
        return $this->matricola;
    }

    public function setMatricola(string $matricola): self
    {
        $this->matricola = sprintf("%'.06d", $matricola);

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function setimageVichFile(File $photoAvatar = null)
    {
        $this->imageVichFile = $photoAvatar;

        // VERY IMPORTANT per VICH:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($photoAvatar) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->createdAt = new \DateTime('now');
        }
    }

    public function getimageVichFile()
    {
        return $this->imageVichFile;
    }

   /*  public function setimagePhoto(File $photoAvatar = null)
    {
        $this->imagePhoto = $photoAvatar;
    }

    public function getimagePhoto()
    {
        return $this->imagePhoto;
    } */


    public function getPhotoAvatar(): ?string
    {
        return $this->photoAvatar;
    }

    public function setPhotoAvatar(?string $photoAvatar): self
    {
        $this->photoAvatar = $photoAvatar;

        return $this;
    }

    
    public function getIsEnforce(): ?bool
    {
        return $this->isEnforce;
    }

    public function setIsEnforce(bool $isEnforce): self
    {
        $this->isEnforce = $isEnforce;

        return $this;
    }

    public function getDateHiring(): ?\DateTimeInterface
    {
        return $this->dateHiring;
    }

    public function setDateHiring(?\DateTimeInterface $dateHiring): self
    {
        $this->dateHiring = $dateHiring;

        return $this;
    }

    public function getDateDismissal(): ?\DateTimeInterface
    {
        return $this->dateDismissal;
    }

    public function setDateDismissal(?\DateTimeInterface $dateDismissal): self
    {
        $this->dateDismissal = $dateDismissal;

        return $this;
    }

    public function getFullCostHour(): ?string
    {
        return $this->fullCostHour;
    }

    public function setFullCostHour(string $fullCostHour): self
    {
        $this->fullCostHour = $fullCostHour;

        return $this;
    }

    public function getCurriculumVitae(): ?string
    {
        return $this->curriculumVitae;
    }

    public function setCurriculumVitae(?string $curriculumVitae): self
    {
        $this->curriculumVitae = $curriculumVitae;

        return $this;
    }

      public function setPdfCvFile(File $curriculumVitae = null)
    {
        $this->pdfCvFile = $curriculumVitae;
    }

    public function getPdfCvFile()
    {
        return $this->pdfCvFile;
    } 

    public function getIntestatarioConto(): ?string
    {
        return $this->intestatarioConto;
    }

    public function setIntestatarioConto(?string $intestatarioConto): self
    {
        $this->intestatarioConto = $intestatarioConto;

        return $this;
    }

    public function getIbanConto(): ?string
    {
        return $this->ibanConto;
    }

    public function setIbanConto(?string $ibanConto): self
    {
        $this->ibanConto = $ibanConto;

        return $this;
    }

    public function getProvincia(): ?Province
    {
        return $this->provincia;
    }

    public function setProvincia(Province $provincia): self
    {
        $this->provincia = $provincia;

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
