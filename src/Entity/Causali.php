<?php

namespace App\Entity;

use App\Repository\CausaliRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CausaliRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("code") 
 */
class Causali
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5)
     * @Assert\Length(
     *      min = 2,
     *      max = 5,
     *  ) 
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=40)
     * @Assert\Length(
     *      max = 40,
     *  ) 
     */
    private $description;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;


    public function __toString(): string
    {
            return (string) $this->getDescription();
    }

    public function getExportData()
    {
        return \array_merge([
            'Codice' => $this->code,
            'Descrizione' => $this->description,
            'Data aggiornamento' => $this->createdAt->format('d.m.Y H:m'),
        ]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

}
