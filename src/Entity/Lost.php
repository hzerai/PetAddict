<?php

namespace App\Entity;

use App\Repository\LostRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LostRepository::class)
 * @ORM\Entity(repositoryClass=AdoptionRepository::class) @ORM\HasLifecycleCallbacks

 */
class Lost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity=Address::class, inversedBy="addressLost")
     * @ORM\JoinColumn(nullable=true)
     */
    private $addressLost;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $updatedBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity=Animal::class, inversedBy="found", cascade={"persist", "remove"})
     */
    private $animal;

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

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

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
/** @ORM\PrePersist */
public function prePersist()
{
    $this->createdAt = new DateTime();
}

/** @ORM\PreUpdate */
public function preUpdate()
{
    $this->updatedAt = new DateTime();
}


public function getAddress(): ?Address
{
    return $this->addressLost;
}

public function setAddress(?Address $addressLost): self
{
    $this->addressLost = $addressLost;

    return $this;
}

public function getAnimal(): ?Animal
{
    return $this->animal;
}

public function setAnimal(?Animal $animal): self
{
    $this->animal = $animal;

    return $this;
}

    
}