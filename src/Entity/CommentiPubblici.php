<?php

namespace App\Entity;

use App\Repository\CommentiPubbliciRepository;
use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity(repositoryClass=CommentiPubbliciRepository::class)
* @ORM\HasLifecycleCallbacks()
*/
class CommentiPubblici
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $email;

    /**
     * @ORM\Column(type="text")
     */
    private $textComment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photoFilename;

    /**
     * @ORM\Column(type="string", length=30, options={"default": "submitted"}) )
     */
    private $state = 'submitted';

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Cantieri::class, inversedBy="commentiPubblici")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cantieri;

    public function __toString(): string
    {
            return (string) $this->getEmail();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getTextComment(): ?string
    {
        return $this->textComment;
    }

    public function setTextComment(string $textComment): self
    {
        $this->textComment = $textComment;

        return $this;
    }

    public function getPhotoFilename(): ?string
    {
        return $this->photoFilename;
    }

    public function setPhotoFilename(?string $photoFilename): self
    {
        $this->photoFilename = $photoFilename;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

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

    public function getCantieri(): ?Cantieri
    {
        return $this->cantieri;
    }

    public function setCantieri(?Cantieri $cantieri): self
    {
        $this->cantieri = $cantieri;

        return $this;
    }

    
}
