<?php

namespace App\Entity;

use App\Repository\ProvinceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProvinceRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("code")
 * @Assert\Callback({"App\Validator\ProvinceValidator", "validate"})  
 */

class Province
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=2, unique=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 2,
     *  )
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=40)
     * @Assert\Length(
     *      min = 4,
     *      max = 40,
     *  )
     */
    private $name;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    public function getIsNotTba(): ?bool
    {
        $isNotTba = false;
        $isNotTba = 'XX' != $this->getCode();
        return (bool) $isNotTba;
    }


    public function __toString(): string
    {
            return (string) $this->getName();
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
        $this->code = strtoupper($code);

        return $this;
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


}
